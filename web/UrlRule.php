<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
namespace yiingine\web;

use \Yii;

/**
 * Override of Yii's UrlRule to make it applicable only to certain languages.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 * */
class UrlRule extends \yii\web\UrlRule
{
    /** @var array the languages this rule applies to. Set to false if
     * it applies to all language.*/
    public $languages = false;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        if(isset($route['languages']))
        {
            $this->languages = $route['languages'];
        }
        
        parent::init();
    }
    
    /**
     * Override of parent implementation to
     * skip the rule if it does not apply to the current language.
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        /* If this rule applies to certain languages and the current language
         * is not part of the list.*/
        if($this->languages && is_array($this->languages) && !in_array(Yii::$app->language, $this->languages))
        {
            return false; //This rule does not apply.
        }

        return parent::createUrl($manager, $route, $params);
    }
    
    /* Do not translate urls for request. This allows urls that specify a language
     * different that the one which was used to generate the url to work.
     * Example: /en/information-légales. */
    
    public function parseRequest($manager, $request)
    {
        return parent::parseRequest($manager, $request);
    }
}
