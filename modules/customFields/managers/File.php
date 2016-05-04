<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

use \Yii;

/** 
 * Manages a CustomField of type file.
 * */
class File extends Base
{        
    /** 
     * @var array a list of allowed extensions, leave empty for no restiction.
     * */
    public $extensions = [];
    
    /**
     * @var callback a callback function($manager) to generate the directory where the files will be saved. 
     * */
    public $directory;
    
    /**
     * @inheritdoc
     * */
    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            \yii\db\ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            \yiingine\db\ActiveRecord::EVENT_AFTER_CLONE => 'afterClone'
        ];
    }
    
    /** 
     * Save files that have been recently added or deleted.
     *  @param $event Event the event parameters.
     *  */
    public function afterSave($event)
    {
        $fileUploader = new \yiingine\widgets\admin\FileListUploader();
        $fileUploader->directory = call_user_func($this->directory, $this);
        $fileUploader->allowedExtensions = $this->extensions;
        $configuration = $this->getField()->getConfigurationArray();
        $fileUploader->maxNumberOfFiles = $configuration['maximumNumberOfFiles'];
        $fileUploader->init();
    
        $fileUploader->fileList = explode(',', $this->owner->{$this->getAttribute()});
        
        $fileUploader->save();
        
        $this->owner->{$this->getAttribute()} = implode(',', $fileUploader->fileList);
    }
    
    /** 
     * Delete files along with the model that owned them.
     * @param $event Event the event parameters.
     * */
    public function beforeDelete($event)
    {
        // Delete the files uploaded with the file uploader.
        $fileUploader = new \yiingine\widgets\admin\FileListUploader();
        $fileUploader->model = $this->owner;
        $fileUploader->directory = call_user_func($this->directory, $this);
        $fileUploader->attribute = $this->getAttribute();
        $fileUploader->init();
        $fileUploader->purge();
    }
    
    /** 
     * Triggered when a customizable model is cloned.
     * @param Event $event the cloning event. $event->owner is the clone.
     * */
    public function afterClone($event)
    {
        $directory = call_user_func($this->directory, $this);
        $tempDir = Yii::getAlias('@webroot/user/temp');
    
        $newFiles = []; // The new file list.
        
        if($this->owner->{$this->getAttribute()})
        {
            // Copy every file to the temp directory.
            foreach(explode(',', $this->owner->{$this->getAttribute()}) as $file)
            {
                $fileName = uniqid().'-'.substr($file, 3);
                //Add a unique id to the file and remove its id.
                copy($directory.'/'.$file, $tempDir.'/'.$fileName);
                $newFiles[] = $fileName;
            }
            
            $event->sender->{$this->getAttribute()} = implode(',', $newFiles); // Save the new file list.
        }
    }
    
    /** 
     * @return mixed an url to this file, many urls if there are multiple files or false if there are no files. 
     * Urls are returned in a format compatible with \yii\helpers\Url::to().
     * */
    public function getFileUrl()
    {
        if(!$value = $this->owner->{$this->getAttribute()})
        {
            return false;
        }
        
        $configuration = $this->getField()->getConfigurationArray();
        
        $directory = str_replace(Yii::getAlias('@webroot'), '', call_user_func($this->directory, $this));
        
        if($configuration['maximumNumberOfFiles'] > 1) // If more than one file can be uploaded.
        {
            $urls = []; //Get the file names an return an url for each name.
                        
            foreach(explode(',', $value) as $file)
            {
                $urls[] = [$directory.'/'.$file];
            }   
            
            return $urls;
        }
        
        return [$directory.'/'.$value];
    }
    
    /** 
     * @param Model $model the model the file belongs to.
     * @return mixed an string containing the font-awesome icon name or an array of icon name if there are multiple files.
     * */
    public function getFaIcon()
    {
        $icons = [];
        
        $fileUrls = $this->getFileUrl();
        if(is_string($fileUrls))
        {
            $fileUrls = [$fileUrls];
        }
        
        foreach($fileUrls as $url)
        {
            switch(strtolower(pathinfo($url[0], PATHINFO_EXTENSION)))
            {
                case 'pdf': $icons[] = 'file-pdf-o'; break;
                case 'xls': $icons[] = 'file-excel-o'; break;
                case 'ppt': $icons[] = 'file-powerpoint-o'; break;
                case 'doc': case 'docx': $icons[] = 'file-word-o'; break;
                case 'jpg': case 'jpeg': case 'png': case 'bmp': $icons[] = 'file-image-o'; break;
                case 'mp3': case 'ac3': case 'ogg': case 'wav': case 'aiff': case 'aac':$icons[] = 'file-audio-o'; break;
                case 'avi': case 'mkv': case 'mpg': $icons[] = 'file-video-o'; break;
                case 'zip': case 'rar': case 'tar': case 'gzip': $icons[] = 'file-archive-o'; break;
                case 'odt': case 'txt': case 'rtf': $icons[] = 'file-text-o'; break;
                default: $icons[] = 'file-o'; // Default icon for files.
            }
        }
        
        return count($icons) == 1 ? $icons[0] : $icons ;
    }
    
    /**
     * @inheritdoc
     * */
    protected function renderInputInternal()
    {
        $configuration = $this->getField()->getConfigurationArray();
        
        return [
            'type' => '\yiingine\widgets\admin\FileListUploader',
            'directory' => call_user_func($this->directory, $this),
            'maxNumberOfFiles' => $configuration['maximumNumberOfFiles'],
            'allowedExtensions' => $this->extensions  
        ];
    }
    
    /**
     * @inheritdoc
     * */
    public function rules()
    {
        $configuration = $this->getField()->getConfigurationArray();
    
        return array_merge(parent::rules(), [
            [$this->getAttribute(), 'string', 'min' => 5],
            [$this->getAttribute(), 'yiingine\validators\FileListValidator', 
                'directory' => call_user_func($this->directory, $this),
                'maxNumberOfFiles' => $configuration['maximumNumberOfFiles'],
                'extensions' => $this->extensions
            ]
        ]);
    }
}
