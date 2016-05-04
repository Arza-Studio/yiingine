<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \Yii;

/**
 * LangBox is a widget that allows the user to switch
 * between supported languages in the web application.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class LangBox extends \yii\base\Widget
{
    /** @var array the route the new language will get submitted to if switched.*/
    public $submitRoute = ['/site/lang'];
    
    /** @var Display mode can be : */
    const CODE = 0; // (default) : "FR | EN | ES"
    const NAME = 1; // "Français | English | Español"
    
    /** @var integer the way languages should be displayed.*/
    public $displayMode = self::CODE;
    
    /** @var boolean to display the current language in the list */
    public $displayCurrent = true;
    
    /** @var switchType can be : */
    const SELECT = 0;
    const LINKS = 1;
    const DROPDOWN = 2;
    const MODAL = 3;
    
    /**@var integer the type of switch to display the different languages.*/
    public $switchType = self::DROPDOWN;
    
    /** @var mixed the separator (html) that shoud be added between languages in LINKS mode. Set to false
     * if no separator is wanted.*/
    public $separator = false;
    
    /** @var array the language that can be selected.*/
    public $languages;
    
    /** @var string the view to render.*/
    public $view = 'langBox';
    
    /**
    * @inheritdoc
    */
    public function run()
    {        
        if(isset($this->languages)) // If a specific list of languages has been set.
        {
            $languages = $this->languages;
        }
         // If a superuser is logged in.
        else if(!Yii::$app->user->isGuest && Yii::$app->user->getIdentity()->superuser)
        {
            // Make all languages available.
            $languages = Yii::$app->params['app.supported_languages'];
        }
        else // Use the available languages,
        {
            $languages = Yii::$app->params['app.available_languages'];
        }
        
        //If there is only one available languages. 
        if(count($languages) < 2)
        {
            return; //Do not display the widget.
        }
        
        // If the current languade must not be displayed
        if(!$this->displayCurrent)
        {
            if(($key = array_search(Yii::$app->language, $languages)) !== false)
            {
                unset($languages[$key]);
            }
        }
        
        $controller = Yii::$app->controller;
        
        $options = []; //The languages that can be selected with the translated urls.
        
        // Removes the /default/index ending that Yii puts at the end of default routes.
        $route = substr($controller->route, -strlen('/default/index')) === '/default/index' &&
                 strpos($controller->route, '/default/index') !== strlen($controller->route) - 14 ? 
            str_replace('/default/index', '', $controller->route) : 
            $controller->route;
        
        // Iterates through all supported languages
        foreach($languages as $language)
        {
            $options[$language] = Yii::$app->urlManager->createUrl(array_merge([$route], $controller->actionParams), $language);
        };
        
        return $this->render($this->view, [
            'currentLanguage' => Yii::$app->language,
            'options' => $options,
            'switchType' => $this->switchType,
            'displayMode' => $this->displayMode,
            'separator' => $this->separator
        ]);
    }
}
