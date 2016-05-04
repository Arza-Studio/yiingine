<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\validators;

use \Yii;

/**
 * Validates a file list received from FileListUploader.
 * A correcly formed file list is separated by the , character stripped of
 * any character that is not -,.,_ or alphanumeric.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class FileListValidator extends \yii\validators\Validator
{
    /** @var string the directory the uploaded files are going to.*/
    public $directory;
    
    /** @var string allowed file extensions separated by commas. */
    public $extensions;
    
    /** @var integer the maximum number of files that can be uploaded.*/
    public $maxNumberOfFiles = 1;
    
    /** @var integer the maximum size in bytes of each file. Set to 0 for no limit.*/
    public $maxSize = 0;
    
    /** @var integer the minimum size in bytes of each file. Set to 0 for no limit.*/
    public $minSize = 0;
    
    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        if(!$model->$attribute) //If the attribute is empty.
        {
            return; // It validates.
        }
        
        if(!is_dir($this->directory)) //If the destination directory does not exist or is not a directory.
        {
            //Do not create it but find the top directory to see if it is writable.
            $topDir = $this->directory;
            while(!is_dir($topDir))
            {
                $topDir = dirname($topDir);
                if($topDir === '/')
                {
                    throw new CException('Invalid directory');
                }
            }
        }
        else
        {
            $topDir = $this->directory;
        }
        
        //If the destination directory is not writable.
        if(!is_writable($topDir))
        {
            $this->addError($model, $attribute, Yii::t(__CLASS__, 'Destination directory ({dir}) is read-only', ['dir' => $topDir]));
        }
        
        // If there are no extensions, skip formatting.
        if($this->extensions !== null)
        {
            // Strip the spaces off the allowed extensions string and explode it into an array.
            $this->extensions = str_replace(' ', '', $this->extensions);
            if(!is_array($this->extensions)) //If extensions were given as a string.
            {
                $this->extensions = str_replace(' ', '', $this->extensions); //Remove white spaces.
                $this->extensions = explode(',', $this->extensions);
            }
        }
        
        // Turn the list of files into an array.
        $files = explode(',', $model->$attribute);
        
        //If more files than permitted have been uploaded.
        if(count($files) > $this->maxNumberOfFiles)
        {
            $this->addError($model, $attribute, 'Cannot upload more than '.$this->maxNumberOfFiles.' files.');
        }
        
        foreach($files as &$file) //For each file in the list.
        {
            if(!$file) //If an empty file name is in the list (ie: ,,)
            {
                $this->addError($model, $attribute, 'file list contains an empty file name.');
            }

            /* Compare the file name with it encoded. If they do not match,
             * this means the file names were not encoded.*/
            if(strcmp($file, \yiingine\libs\Functions::encodeFileName($file)))
            {
                //$this->addError($model, $attribute, 'File name is illegal.');
            }
            
            //If the extension is not part of the list of allowed extensions.
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if($this->extensions && !in_array($ext, $this->extensions))
            {
                $this->addError($model, $attribute, 'Forbidden file extension for '.$file);
            }
            
            // Build the file path with the file in its destination directory or the temp directory.
            $filePath = !is_file($this->directory.'/'.$file) ? Yii::getAlias('@webroot/user/temp').'/'.$file : $this->directory.'/'.$file;
            
             //If for some reason, the file does not exist.
            if(!is_file($filePath))
            {
                $this->addError($model, $attribute, Yii::t(__CLASS__, 'File missing'));
            }
            
            // If the size of the file is below the minimum allowed size.
            if($this->minSize > 0 && filesize($filePath) < $this->minSize)
            {
                $this->addError($model, $attribute, Yii::t(__CLASS__, '{file} is too small, minimum file size is {minSizeLimit}.', ['minSizeLimit' => $this->minSize, 'file' => $file]));
            }
            // If the size of the file is above the maximum allowed size. 
            else if($this->maxSize > 0 && filesize($filePath) > $this->maxSize)
            {
                $this->addError($model, $attribute, Yii::t(__CLASS__, '{file} is too large, maximum file size is {sizeLimit}.', ['sizeLimit' => $this->maxSize, 'file' => $file]));
            }
        }
    }
}
