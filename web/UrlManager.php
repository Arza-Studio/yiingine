<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\web;

use \Yii;

/**
* An URL manager that adds the current language (with locale if it is provided)
* as a first parameter to the url.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class UrlManager extends \yii\web\UrlManager
{        
    /**@var array the list of available languages. Those are the languages that the site displays.*/
    private $_availableLanguages;
    
    /**@var array the list of supported languages. Those are the languages that are stored in database.*/
    private $_supportedLanguages;
    
    /** @var array folders the language should not be added to. */
    public $exclude = ['images', 'css', 'js', 'user', 'fonts', 'documents'];
    
    /** @var string the name of the class for the url rules.*/
    public $urlRuleClass = 'UrlRule';
    
    /**
     * @inheritdoc
     */
    public $ruleConfig = ['class' => 'yiingine\web\UrlRule'];
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        parent::init(); //Calls the parent.
        
        $this->_supportedLanguages = Yii::$app->params['app.supported_languages'];
        
        /* If a user is logged in an an administrator, the disabled languages (those
         * supported but not available) are made available. */
        $this->_availableLanguages = !Yii::$app->user->isGuest && Yii::$app->user->getIdentity()->superuser ? Yii::$app->params['app.supported_languages'] : Yii::$app->params['app.available_languages'];
    }
    
    /**
     * Override of parent implementation to parse the url for a language present as the first
     * item and set it if that is the case.
     * @inheritdoc
     */
    public function parseRequest($request)
    {
        // Remove the baseUrl from the url.
        $url = $request->baseUrl ? str_replace($request->baseUrl.'/', '', $request->url): substr($request->url, 1);
        
        /* Get the end of the language part of the url. The length is not fixed because
         * the language may contain a locale. */
        $end = strpos($url, '/', 0);
        $language = $end ? substr($url, 0, $end): substr($url, 0);
        
        
        /* If there is more than one supported language, the language is first in the url 
         * and it is part of the available languages. */
        if(count($this->_supportedLanguages) > 1 &&  
            in_array($language, $this->_availableLanguages))
        {   
            if(Yii::$app->language != $language) // If the language changed.
            {
                // Set the language and save it as a session variable.
                Yii::$app->language = $language;
                Yii::$app->session['language'] = $language;
                
                /* Since url is parsed quite late into the processing of a request, some
                 * items have already been translated with the previous language. Refresh
                 * the page to apply changes to the language.*/
                Yii::$app->response->redirect($request->url);
            }
            
            $baseUrl = $request->baseUrl; // Save the current base url.
            // Make the language part of the base url so it gets thrown away.
            $request->setBaseUrl($baseUrl.'/'.$language);
            $route = parent::parseRequest($request); // Get the route without the language.
            $request->setBaseUrl($baseUrl); // Reset the base url.
            
            return $route;
        }
        // Else there is no supported language in the url, return it as is.
        
        /* The language was not part of the url so the current language is selected. Inform
         * the user that the canonical page is actually the one where the language is
         * specified.*/
        if($this->getCanonicalUrl() !== false)
        {
            Yii::$app->view->registerLinkTag(['rel' => 'canonical', 'href' => $this->getCanonicalUrl()]);
        }
        
        return parent::parseRequest($request);    
    }
    
    /**
     * Override of parent implementation to prepend the language to a generated URL when its
     * first resource is not part of the excluded folders list.
     * @inheritdoc
     * @param string $forceLanguage the language to generate the URL in. Set to null for the current language or to an empty
     * string if no language should be added..
     */
    public function createAbsoluteUrl($params, $scheme = null, $forceLanguage = null)
    {
        $url = $this->createUrl((array)$params, $forceLanguage);
        
        if(strpos($url, '://') === false) 
        {
            $url = $this->getHostInfo().$url;
        }
        if(is_string($scheme) && ($pos = strpos($url, '://')) !== false)
        {
            $url = $scheme . substr($url, $pos);
        }
        
        return $url;
    }
    
    /**
     * Override of parent implementation to prepend the language to a generated URL when its
     * first resource is not part of the excluded folders list.
     * @inheritdoc
     * @param string $forceLanguage the language to generate the URL in. Set to null for the current language or to an empty
     * string if no language should be added..
     */
    public function createUrl($params, $forceLanguage = null)
    {                
        if($forceLanguage)
        {
            $currentLanguage = Yii::$app->language; // Save the current language.
            Yii::$app->language = $forceLanguage;
        }
        
        /* Get the url normaly. This needs to be done before adding the language otherwise
         * rewrite rules stop working.*/
        $url = parent::createUrl($params);
        
        if($forceLanguage)
        {
            Yii::$app->language = $currentLanguage; // Restore the language.
        }
        
        // If the route starts with a destination that is in the exclusion list.
        if(in_array(substr($params[0], 0, strpos($params[0], '/')), $this->exclude))
        {
            return $url; // Return the url without adding the language.
        }
        
        if($forceLanguage === '') // If the language should no be added to the url.
        {
            return $url;
        }
        
        $language = $forceLanguage ? $forceLanguage : Yii::$app->language;
        
        /* If there is more than one supported language, the language is first in the url 
         * and it is part of the available languages. */
        if(count($this->_supportedLanguages) > 1 && 
            in_array($language, $this->_availableLanguages) ||
            $forceLanguage)
        {            
            $baseUrl = Yii::$app->request->baseUrl;
            return $baseUrl ? str_replace($baseUrl, "$baseUrl/$language", $url) : "/$language$url";
        }
        // Else there is only one supported language.
        
        return $url;
    }
    
    /** @return mixed the canonical url or false if it is not different from the current url.*/
    public function getCanonicalUrl()
    {    
        /* The language was not part of the url so the current language is selected.
         * The canonical page is actually the one where the language is
         * specified.*/
        if(count($this->_supportedLanguages) > 1 && strpos(Yii::$app->request->url, Yii::$app->request->baseUrl.'/'.Yii::$app->language) !== 0) // If many languages are present and they are not part of the url.
        {
            return Yii::$app->request->baseUrl.'/'.Yii::$app->language.'/'.Yii::$app->request->pathInfo.(Yii::$app->request->queryString ? '?'.Yii::$app->request->queryString :'');
        }
        
        return false; // No canonical url.
    }
    
    /**
     * Override of parent implementation to load rules from
     * the database.
     * @inheritdoc
     */
    protected function buildRules($rules)
    {                                
        // Attempt to retrieve database rules from cache.
        if(($dbRules = Yii::$app->cache->get('dbUrlRewriteRules')) === false)
        {
            $dbRules = []; // Rules were not found in cache.
            
            // Iterate through all enabled url rules.
            foreach(\yiingine\models\UrlRewritingRule::find()->where(['enabled' => 1])->orderBy('position')->all() as $model)
            {
                // Create a rule and initialize it.
                $rule = [
                    'route' => $model->route,
                    'defaults' => eval('return '.$model->defaults.';'),
                    'encodeParams' => $model->encode_params,
                    'mode' => $model->mode,
                    'suffix' => $model->suffix === '' ? null: $model->suffix,
                    'verb' => $model->verb === '' ? null: $model->verb,
                    'pattern' => $model->pattern,
                    'host' => $model->host === '' ? null :$model->host,
                ];
                
                $languages = str_replace(' ', '', $model->languages);
                $rule['languages'] = $languages ? explode(',', $languages): false;
                
                /* If a rule with this pattern already exist, this means the pattern
                 * for two different languages is the same.*/
                if(isset($dbRules[$model->pattern])
                    && is_array($dbRules[$model->pattern]['languages'])
                    && is_array($rule['languages'])
                    && $dbRules[$model->pattern]['route'] == $rule['route']
                    && !array_diff($dbRules[$model->pattern]['defaults'], $rule['defaults']))
                {
                    // Combine the languages.
                    $dbRules[$model->pattern]['languages'] = array_merge($dbRules[$model->pattern]['languages'], $rule['languages']);
                    continue; //Do not add a new rule to the list.
                }
                else
                {
                    $dbRules[$model->pattern] = $rule; //Add this rule.
                }
            }
            
            Yii::$app->cache->set('dbUrlRewriteRules', $rules, 0, new \yiingine\caching\GroupCacheDependency(['UrlRewritingRule']));
        }
        
        return parent::buildRules(array_merge($dbRules, $rules));
    }
}
