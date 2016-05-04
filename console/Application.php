<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\console;

/**
 * Yii2's \yii\console\Application adapted for use with the Yiingine.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class Application extends \yii\console\Application
{            
    /**
     * Returns the human adapted label for the module.
     * @return string the label
     */
    public function getLabel() { return ''; }
    
    /** 
     * Verifies that an application parameter exists and return its value.
     * @param string $name the name of the parameter.
     * @param mixed $default value to be returned if the configuration entry does not exit.
     * @return mixed the value of the parameter or false if it does not exist.
     * */
    public function getParameter($name, $default = false)
    {
        return isset($this->params[$name]) ? $this->params[$name] : $default;
    }
    
    /**
     * The first language in the supported_languages configuration entry defines
     * the language that is not translated (the one stored in the fields of the models)
     * unless the sourceLanguage is present within the supported languages array in which case it
     * becomes the language that is not translated.
     * @return the language that is not translated.
     * */
    public function getBaseLanguage()
    {
        return in_array($this->sourceLanguage, $this->params['app.supported_languages']) ?
        $this->sourceLanguage : $this->params['app.supported_languages'][0];
    }
}
