<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets\admin;

use \Yii;

/**
 * FileListUploader is a widget for uploading files through ajax and associating them with a
 * model. By default, it limits the list to one file, but this behavior can be adapted through
 * an attribute. This widget is dependent upon the controller/action admin/uploadFile since
 * the uploading is done through ajax. This widget would be capable of intercepting the request
 * but since the result would be rendered with the layout active, this would not play out
 * with the ajax upload library which expects a JSON response.
 * 
 * The uploading works by sending the files to a temporary location on the server and prepending
 * them with the session id. When the model is saved and calls the save method of this widget,
 * the files are moved from the temporary directory its destination. To uniquely identify each
 * file, its name is prepended with its owner model's id.
 * 
 * The reason why a temporary directory is used is to prevent the accumulation of dirty
 * files in the end directory. A dirty file may happen if the use uploads a file and closes
 * the form, if validation fails, etc.
 * 
 * On the model, the names of the files are cleaned up from illegal characters and saved
 * as a list separated by the | character.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class FileListUploader extends \yii\base\Widget
{
    /** @var CActiveRecord the model the files belong to.*/
    public $model;
    
    /** @var string the field that holds the file list.
     * @deprecated since 04/04/2012. */
    public $fieldName;
    
    /** @var string the name of the attribute that holds the file list. If the files should
     * not be set to this attribute, do not set it and use fileList instead.*/
    public $attribute;
    
    /** @var boolean only register the script but do not create the widget. The reason this
     * mechanism was implemented is to allow forms that use this widget to upload correctly
     * even if the page they were requested from did not contain orginally contain the widget.
     * See #872 and #963.*/
    public $scriptsOnly = false;
    
    /** @var string the directory that will hold the files.*/
    public $directory;
    
    /** @var string the url to the file directory. */
    public $directoryUrl;
    
    /** @var array the list of allowed extensions for the files. */
    public $allowedExtensions = [];
    
    /** @var string the directory where the uploaded files are temporarily kept. */
    public $tempUploadDirectory = '';
    
    /** @var integer the maximum number of files that can be uploaded. */
    public $maxNumberOfFiles = 1;
    
    /** @var integer the maximum size in bytes of each file. Set to 0 for no limit.*/
    public $maxSize = 0;
    
    /** @var integer the minimum size in bytes of each file. Set to 0 for no limit.*/
    public $minSize = 0;
    
    /**@var array the list of files to process.*/
    public $fileList;
    
    /** @var integer the permissions for the uploaded files.*/
    public $permissions = 775;
    
    /** @return array the list of actions used by this widget.*/
    public static function actions()
    {
        return [
            'fileListUploader.upload' => ['class' => '\yiingine\widgets\admin\FileListUploaderUploadAction']
        ];    
    }
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        //Publish assets and registers script.
        if(!(defined('CONSOLE') && CONSOLE))
        {
            $assetUrl = Yii::$app->assetManager->publish(Yii::getAlias('@yiingine/vendor/valums-file-uploader'))[1];
            //Yii::app()->getClientScript()->registerCssFile($assetUrl.'/fileuploader.css', 'screen, projection');
            Yii::$app->view->registerJsFile($assetUrl.'/fileuploader.js');
        }
        
        if($this->model && !$this->attribute) // If the model has been provided but not the attribute.
        {
            throw new \yii\base\Exception('Missing attribute');
        }
        
        //If only the scripts were wanted, skip the rest of the initialization process.
        if($this->scriptsOnly) { return; }
        
        if(!is_integer($this->maxNumberOfFiles))
        {
            throw new \yii\base\Exception('Maximum number of files must be an integer.');
        }
        
        if(isset($this->fieldName))
        {
            trigger_error("fieldName is deprecated in FileListUploader", E_USER_NOTICE);
            $this->attribute = $this->fieldName;
        }
        
        if(!$this->tempUploadDirectory) //If no temporary upload directory is defined.
        {
            //Use the default one.
            $this->tempUploadDirectory = Yii::getAlias('@webroot/user/temp');            
        }
        
        if(!is_dir($this->tempUploadDirectory)) //If the temporary upload directory does not exist.
        {
            if(!mkdir($this->tempUploadDirectory)) //Attempt to create the temporary upload directory.
            {
                throw new \yii\base\Exception('Could not create temporary upload directory at '.$this->tempUploadDirectory);
            }
        }
        else if(!is_writable($this->tempUploadDirectory)) //If the temporary directory is not writable.
        {
            throw new \yii\base\Exception('Temporary upload directory is not writable');
        }
        
        if(!$this->directoryUrl) //if the directory url is not defined, build it from the directory's path. 
        {
            $this->directoryUrl = str_replace(Yii::getAlias('@webroot'), '', $this->directory); 
        }       
    }
    
    /**Override of parent implementation to generate a more unique id. Because this widget is
     * used in ajax forms and the id is based on the number of instances of this widget, this
     * creates conflicts.*/
    public function getId($autoGenerate = true)
    {
        $id = parent::getId(false);
        if($id === null && $autoGenerate)
        {
            $id = uniqid();
            $this->setId($id);
        }
        return $id;
    }
    
    /**
     * Saves the uploaded files by moving them from the temporary directory to the destination
     * and removes the files that have been deleted.
     * @return boolean if saving was a success.
     */
    public function save()
    {
        //Helper variables.
        $model = $this->model;
        
        /*As decided in #152, lists are now separated with a comma.
         * We thus need to convert from the old format.*/
        if(isset($this->attribute))
        {
            $fileList = strpos($model->{$this->attribute}, '|') ? explode('|', $model->{$this->attribute}) : explode(',', $model->{$this->attribute});
        }
        else
        {
            $fileList = $this->fileList;
        }
        
        $tempUploadDirectory = Yii::getAlias('@webroot/user/temp');
        
        if($model instanceof \yii\db\ActiveRecord)
        {
            if(!$this->model->primaryKey) //If the model is new.
            {
                throw new \yii\base\Exception('Cannot save files on a model which has not been saved already.');
            }
            else //Use the current primary key.
            {
                $modelPk = $model->primaryKey;
            }
        }
        else
        {
            $modelPk = null; // Do not use a primary key because there is none.
        }

        if(!empty($fileList) && !file_exists($this->directory)) //If the destination directory does not exist.
        {
            if(!mkdir($this->directory, 0755, true)) //Create it.
            {
                //Creation failed.
                //The widget cannot proceed, throw an error.
                throw new \yii\web\ServerErrorHttpException('Destination directory '.$this->directory.' cannot be created');
            }
        }
        
        // For each file in the file list (delimited by ,).
        foreach($fileList as &$file)
        {   
            if(!$file) //If the file name is empty.
            {
                continue; //Skipt it.
            }

            //Build the paths.
            $tempFile = $this->tempUploadDirectory.'/'.$file;
            
            /* If the file does not exist in the temporary directory it is probably a file that 
             was uploaded previously. If the file is in the temporary directory AND the destination
             directory, the user is replacing the file with one with the same name so we
             treat is as a new file. This only applies to files for ActiveRecords. */
            if(!file_exists($tempFile) && $modelPk !== null)
            {
                if(file_exists($this->directory.'/'.$file))
                {
                    $id = explode('-', $file, 2); //Get the id of the file.
                    //Check if the file belongs to that model.
                    if($id[0] != $modelPk)
                    {
                        /* If the id do not match, this means a model is reusing a file
                         * from another model. In this case, copy the file and rename it.*/
                         copy($this->directory.'/'.$file, $this->directory.'/'.$modelPk.'-'.$id[1]);
                         $file = $modelPk.'-'.$id[1]; //Rename the file.
                    }
                    
                    //File already exists and has not been modified so skip it.
                    continue;
                }
                //If file does not exist in the temporary directory or the destination directory.
                
                //The file was not uploaded and a file is not on the server.
                continue; //Skip it.
            }
            // If there is no file in the temporary directory.
            else if(!file_exists($tempFile))
            {
                continue; // Do not do anything.
            }
            /* A file that is being replaced will not yet have the id in front of it so it
             * will not match an existing file.*/

            /* File does not exist so it is in the temp directory, strip the unique id off the 
             * file name to get the destination name. by removing characters up to the first "-".*/
            $file = ($modelPk === null ? '' : $modelPk.'-').substr($file, strpos($file, '-') + 1);
            
            //Check if another item by that name exists in the fileList array.
            $fileNames = array_count_values($fileList); //Count the number of each value.
            //If other files with that name exist.
            if(isset($fileNames[$file]) && $fileNames[$file] > 1)
            {
                $position = $fileNames[$file];
                do
                {
                    //Add an the position of this file right before its extension.
                    $name = str_replace('.', '-'.$position++.'.', $file);
                } //Check if that name also exists.
                while(isset($fileNames[$name]));
                
                $file = $name;
            }

            /*Moves the temp file to the destination directory and rename it. Also overwrites
             * any preexisting file with that name.*/ 
            if(!rename($tempFile, $this->directory.'/'.$file))
            {
                throw new \yii\web\ServerErrorHttpException('Moving file failed');    
            }
            
            // Make sure everyone can modify the file.
            chmod($this->directory.'/'.$file, $this->permissions);
        }

        /*Some file names have changed if they have been moved from the temporary directory,
         * save them to the model or this file list if $this->attribute is not set..
         */
        isset($this->attribute) ? $this->model->{$this->attribute} = implode(',', $fileList): $this->fileList = $fileList;

        return true; //Saving was sucessful;
    }
    
    /**
     * Cleans up files that are no longer referred to by a model and its translations. 
     * Must be called once the model has been saved..
     * */
    public function clean()
    {
        $fileList = $this->model ? $this->model->{$this->attribute} : $this->fileList;
        $fileList = is_array($fileList) ? $fileList: explode(',', $fileList);
        
        if(!is_dir($this->directory))
        {
            return;
        }
        
        foreach(scandir($this->directory) as $file)
        {
            // If this file has been removed from the list and exists in the directory.
            if(!in_array($file, ['.', '..']) && !in_array($file, $fileList) && is_file($this->directory.DIRECTORY_SEPARATOR.$file))
            {
                unlink($this->directory.DIRECTORY_SEPARATOR.$file); // Delete it.
            }
        }
    }
    
    /**
     * Deletes all files associated with the model.
     */
    public function purge()
    {           
        if(!is_dir($this->directory))
        {
            return;
        }
        
        $fileList = $this->model ? $this->model->{$this->attribute} : $this->fileList;
        
        foreach(is_array($fileList) ? $fileList :explode(',', $fileList) as $file)
        {
            $file = $this->directory.DIRECTORY_SEPARATOR.$file;
                
            if(is_file($file))
            {
                unlink($file);
            }
        }
        
        //Remove the directories that contained this file until a directory which contains other things is encountered.
        $dir = $this->directory;
        while(count(scandir($dir)) < 3) //scandir returns . and ..
        {
            rmdir($dir);
            $dir = dirname($dir);
        }
        
        $this->fileList = [];
    }
    
    /**
    * @inheritdoc
    */
    public function run() 
    {                        
        return $this->render('fileListUploader');
    }
}

/** 
 * This action allows a user to upload a file that gets saved to 
 * a temporary location with the user's session id at the beginning.
 * It can then be retrieved by another part of the application.
 * This method uses the vendor module valumns-file-uploader.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> 
 * */
class FileListUploaderUploadAction extends \yii\base\Action
{
    /**
     * @inheritdoc
     * @param $qqfile string the name of the uploaded file.
     */
    public function run($qqfile)
    {
        if(!Yii::$app->request->isPost) //If this is not a post request.
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }
        
        //Builds the path to the temp upload directory.
        $tempUploadDirectory = Yii::getAlias('@webroot/user/temp');
        
        //Computes the size of the file.
        $size = isset($_SERVER["CONTENT_LENGTH"]) ? $_SERVER["CONTENT_LENGTH"] : 0;
        $return = array(); //A JSON array expected by the file uploader.
        
        if(!file_exists($tempUploadDirectory)) //If the destination directory does not exist.
        {
            if(!@mkdir($tempUploadDirectory, 0644, true)) //Create it.
            {
                //Creation failed.
                throw new \yii\web\ServerErrorHttpException('Temporary directory '.$tempUploadDirectory.' cannot be created');
            }
        }
        
        if (!is_writable($tempUploadDirectory)) //If the upload directory is read-only.
        {
             $return['error'] = 'Server error. Upload directory isn\'t writable.';
        }
        else if (!$qqfile) //If no file was uploaded.
        {
            $return['error'] = 'No files were uploaded.';
        }
        else if ($size == 0) //If the file is empty.
        {
            $return['error'] = 'File is empty';
        }  
        else if ($size > \yiingine\libs\Functions::returnBytes(ini_get('post_max_size'))) //If the file is too large.
        {
            $return['error'] = 'File is too large';
        }
        else //Everything checked out OK.
        {
            /* Loops trough all files in the temporary upload directory
             * an delete them if they are too old. This prevents the directory
             * from accumulating files.*/
            foreach(scandir($tempUploadDirectory) as $file)
            {
                if(in_array($file, array('htaccess', '.htaccess'))) // Do not delete these files.
                {
                    continue;
                }
                
                $filePath = $tempUploadDirectory.'/'.$file; //Builds the file path.
                $time = filemtime($filePath); //Gets the modification time.
                if(time() - $time > 3600 * 48) //If that time is older than two days.
                {
                    if(!is_dir($filePath) && !unlink($filePath)) //Delete the file.
                    {
                        throw new \yii\web\ServerErrorHttpException('Could not clean up temp directory!');
                    }
                }
            }
            
            /* Builds the file name by prefixing it with $prefix and then
             * encoding its name. */
            $fileName = \yiingine\libs\Functions::encodeFileName($qqfile);
            //The file is prefixed with a unique id to avoid conflicts between users.
            $fileName = uniqid().'-'.$fileName;
            
            $input = fopen("php://input", "r"); //Opens the uploaded file.
            // Opens the target location. 
            $target = fopen($tempUploadDirectory.'/'.$fileName, "w");
            //If copying the file failed.
            if(stream_copy_to_stream($input, $target) != $size)
            {
                throw new \yii\web\ServerErrorHttpException('Failed to copy file.');
            }
            
            //Closes the file objects.
            fclose($target);
            fclose($input);
            
            // Important security measure: prevent anyone from executing that file.
            chmod($tempUploadDirectory.'/'.$fileName, 0644);
            
            //Builds the return array.
            $return['fileName'] = $fileName; 
            $return['success'] = true;
        }
        
        //Sets the content-type header to JSON because this is what the file uploader expects.
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        return $return;        
    }
}
