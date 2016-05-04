<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\i18n;

use \Yii;

/**
 * This class describes a PhpMessageSource for the Yiingine. With this class, messages
 * can come from two sources:
 * 
 * #THE APPLICATION
 * Looks within the client application for the translation.
 * 
 * #THE Yiingine
 * Look within the yiingine for the translation.
 * 
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class PhpMessageSource extends \yii\i18n\PhpMessageSource
{
    /**
     * @var array() Keeps a list of message files we have confirmed the existence of.
     */
    private $_files = []; 
    
    /**
    * @inheritdoc
    */
    public static function hasTranslation($category, $message, $language)
    {
        if(\Yii::$app->sourceLanguage == $language)
        {
            return true;
        }
        else
        {
            return \Yii::t($category, $message, $language) != $message;
        }
    }
    
    /**
    * @inheritdoc
    */
    protected function loadMessages($category, $language)
    {
        // If the category does not match this pattern.
        if($category != __CLASS__.'.'.$language)
        {
            /* The source will be a PHP file. That file can come from either
             * the client application or the engine. */
            
            // If the $category does not belong to a module or class, we handle it.
            if(strpos($category, '.') === false && strpos($category, '\\') === false)
            {                        
                /* Since PhpMessageSource caches the file paths of a message category in a private attribute,
                 * it will only build a path once. As a consequence, we have to look ourselves
                 * for the file and then hand it to PhpMessageSource once we are sure it exists.
                 * In the process we also do some caching.
                 */
                // Have we encountered that category in the past?
                if(!isset($this->_files[$category]))
                {
                    // The file path to that category is not in cache.
                    // First set basePath to point to the client application.
                    $this->basePath = Yii::getAlias('@app/messages');
                    
                    // Does the client application not have that message file?
                    if(!is_file($this->basePath.'/'.$language.'/'.$category.'.php'))
                    {
                        // The client application did not contain the messages we are looking for.
                        $this->basePath = Yii::getAlias('@yiingine/messages');
                        
                        // Does the engine not have that message file?
                        if(!is_file($this->basePath.'/'.$language.'/'.$category.'.php'))
                        {
                            // This message file does not exist.
                            return [];
                        }
                    }                   
                    // is_file is a costly operation so the result is kept in an array.
                    $this->_files[$category] = $this->basePath;
                }
                
                //Sets the basePath to where the request category is.
                $this->basePath = $this->_files[$category];
            }
            else // The messages belongs to a module or a class, look for an override in the application.
            {
                if(strpos($category, '.') === false) // If the category is just the name of the class.
                {
                    $class = $category;
                    $cat = substr($class, strrpos($class, '\\') + 1);
                }
                else // The category is given as "class.category".
                {
                    // Get the class'name.
                    $class = substr($category, 0, strpos($category, '.'));
                    $cat = substr($category, strpos($category, '.') + 1);
                    
                    if($cat == $class) // If the category is the name of the class itself (class.class).
                    {
                        $cat = substr($class, strrpos($class, '\\') + 1);
                    }
                }
                
                // Get the file path of the class.
                if($class = (new \ReflectionClass($class))->getFileName())
                {
                    $this->basePath = dirname($class).'/messages';
                    $this->_files[$category] = $this->basePath;
                    $category = $cat;
                }
            }
        }
        
        // Attempt to load the messages from the selected source.
        return parent::loadMessages($category, $language);
    }
}
