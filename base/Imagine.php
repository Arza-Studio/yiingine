<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\base;

use \Yii;
use \yii\imagine\Image;


/**
 * A component wrapper over the yii2-imagine library to move converted images
 * to the a web accessible folder when they have been modified.
 * 
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 * */
class Imagine extends \yii\base\Component
{
    /** @var string the name of the folder that contains the modified images. */
    public $folderName = 'imagineImages';
    
    /** @var string the web accessible path where converted images will reside.
     * when they have been modified. If not specified, a folder will be created in
     * the asset manager's folder.*/
    public $basePath;
    
    /** @var string the url through which images will be accessible. */
    public $baseUrl;
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        if(!isset($this->basePath)) // If no path has been specified during initialization.
        {
            $this->basePath = Yii::$app->assetManager->basePath;
            
            if(!is_dir($this->basePath.DIRECTORY_SEPARATOR.$this->folderName))
            {
                // Create the folder where the modified images will be stored.
                mkdir($this->basePath.DIRECTORY_SEPARATOR.$this->folderName);
            }
        }
        
        if(!isset($this->baseUrl)) // If no url has been specified during initialization.
        {
             $this->baseUrl = Yii::$app->assetManager->baseUrl;
        }
    }
    
    /**
     * Creates a thumbnail image. The function differs from `\Imagine\Image\ImageInterface::thumbnail()` function that
     * it keeps the aspect ratio of the image.
     * @param string $fileName the image file path or path alias.
     * @param integer $height the height in pixels to create the thumbnail.
     * @return string the url to the thumbnail.
     */
    public function getThumbnail($fileName, $height)
    {       
        $fileModificationTime = filemtime($fileName);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        
        $thumbnail = $this->basePath.DIRECTORY_SEPARATOR.$this->folderName.DIRECTORY_SEPARATOR.md5($fileName.$height.'thumbnail').$fileModificationTime.'.'.$extension;
        
        if(is_file($thumbnail)) // If a previously thumbnailed file exists.
        {
            // Reuse the existing thumbnail.
            return str_replace($this->basePath, $this->baseUrl, $thumbnail);
        }
        else // The file has changed.
        {
            // Check all files in the folder for ones matching the old thumbnailed file.
            foreach(glob($this->basePath.DIRECTORY_SEPARATOR.$this->folderName.DIRECTORY_SEPARATOR.md5($fileName.$height.'thumbnail').'*') as $file)
            {
                if(is_file($thumbnail))
                {
                    unlink($thumbnail); // The file has been modified so delete the old one.
                }
            }
        }
        
        $size = getimagesize($fileName); // Get image size.
        $ratio = $size[0] / $size[1]; // Get image size ratio.
        
        // A new thumbnail must be created.
        Image::thumbnail($fileName, ceil($height * $ratio), $height)->save($thumbnail, ['quality' => 100]);
        
        // Return the url to the new thumnail.
        return str_replace($this->basePath, $this->baseUrl, $thumbnail);
    }
    
    /** 
     * Flushes all the images contained in $path. 
     * */
    public function flush()
    {
        if(!$this->basePath) // If no base path has been specified (the component was not initialized).
        {
            throw new \yii\base\Exception('Base path not specified.');
        }
        
        \yii\helpers\FileHelper::removeDirectory($this->basePath.DIRECTORY_SEPARATOR.$this->folderName);
    }
}
