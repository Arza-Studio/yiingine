<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\behaviors;

use \Yii;

/**
 * This behavior sets the application's language according to the request parameters and
 * the configuration.
 */
class ApplicationLanguageBehavior extends \yii\base\Object
{
    /** 
     * @inheritdoc
     * */
    public function init()
    {
        // Register this object to handle the before request event.
        \Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, [$this, 'beforeRequest']);
        
        parent::init();
    }
 
    /**
     * Load configuration from the database generate it at runtime.
     * @param Event $event the event that triggered the handler.
     */
    public function beforeRequest($event)
    {        
        $params = Yii::$app->params;
        
        // If there is only one supported language.
        if(count($params['app.supported_languages']) < 2)
        {
            // If the wrong language was set earlier in ApplicationParametersBehavior.
            if(implode($params['app.supported_languages']) !== Yii::$app->language)
            {
                // This could be due to a conflict with a cookie from another site running the eninge on the same domain.
                Yii::$app->language = implode($params['app.supported_languages']);
                Yii::$app->request->cookies->remove('language');
                Yii::$app->session->remove('language');
                
                /* We need to reload the configuration entries to have them
                 * translated to the correct language.*/
                
                return $this->owner->onBeginRequest($event); // Works but could have unknown side effects!
            }
            
            return; // Nothing else to do.
        }
        
        $session = Yii::$app->session;
        
        // Check if some languages in available_languages are not in supported_languages.
        if(count(array_diff($params['app.available_languages'], $params['app.supported_languages'])))
        {
            // All available_languages must be part of the supported_languages.
            throw new \yii\base\Exception('The following languages are set but are not part of the supported languages: '.implode(',', $diff));
        } 
             
        // LANGUAGE DETECTION
        if(!$session['language']) //If no session variable for the language has been set.
        {        
            // Save the previous language to know if it has changed.
            $previousLanguage = Yii::$app->language;  
            
            // If the available_language configuration item is set.
            if(isset($params['app.available_languages']))
            {
                /* Extract the language preference from the HTTP header if
                 * the configuration item use_language_prefs is set.*/
                if(isset($params['app.use_language_prefs']) && 
                    $params['app.use_language_prefs'] == '1' && 
                    $pref = Yii::$app->request->getPreferredLanguage())
                {
                    // If the preferred language is not a supported language.
                    if(!in_array($pref, $params['app.available_languages']))
                    {
                        /* It's probably that the locale is different so try to match 
                         * the same language.*/
                         
                        // Extracts the language from the language_locale.
                        $lang = explode('_', $pref);
                        $lang = $lang[0];
                        $found = false; //Have we found the language?

                        // Loops through the available languages.
                        foreach($params['app.available_languages'] as $slang)
                        {
                            if(strpos($slang, $lang) === 0) //If the two languages match.
                            {
                                $pref = $slang; //Use that language.
                                $found = true; //We have found a language.
                                break;
                            }
                        }
                        if(!$found) //If we could not find a language.
                        {
                            //Use the first available language
                            $pref = $params['app.available_languages'][0];                          
                        }
                    }
                }
                else
                {
                    /* Language preference is not set within the HTTP header, pick
                     * a default language.*/
                    
                    // Use the first language in the available_languages list by default.
                    $pref = $params['app.available_languages'][0];
                    
                    /* If the 'app.per_domain_preferred_language' configuration entry is 
                     * set use it to set a domain dependent preferred language.*/
                    if(isset($params['app.per_domain_preferred_language']))
                    {
                        // Remove all white spaces.
                        $map = str_replace(' ', '', $params['app.per_domain_preferred_language']);
                        
                        /* This configuration entry must follow this structure:
                         * domain1:language,domain2:language.*/
                        foreach(explode(',', $map) as $entry)
                        {
                            $entry = explode(':', $entry);
                            
                            if(count($entry) != 2) //Validate the format.
                            {
                                throw new \yii\base\Exception('"yiingine.per_domain_preferred_language" is malformed.');
                            }
                            //If the server name matches this entry.
                            else if(Yii::$app->request->serverName == $entry[0])
                            {
                                $pref = $entry[1]; //Set the prefferred language.
                                break;
                            }
                        }
                    }
                }
            }
            else
            {
                /* Configuration item is not set so ignore user preferences and 
                 * revert to sourceLanguage.*/
                $pref = Yii::$app->sourceLanguage;
            }
            
            Yii::$app->language = $pref; // Set the active language.
            $session['language'] = $pref; // Sets the preferred language as a session variable.
            
            // Set the language as a cookie so it is remembered between site accesses.    
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'language',
                'expire' => 0,
                'value' => Yii::$app->language
            ]));
            
            if($previousLanguage != $pref) // If the language has changed.
            {
                /* We need to reload the configuration entries to have them
                 * translated to the correct language.*/
                
                return $this->owner->onBeginRequest($event); //Works but could have unknown side effects!
            }
        }
        // Else language was set in ApplicationParametersBehavior.
        
        // Logged in administrators get access to all supported languages.
        $availableLanguages = !Yii::$app->user->isGuest && Yii::$app->user->getIdentity()->superuser ? $params['app.supported_languages'] : $params['app.available_languages']; 
        
        //Verify that the current language is within the supported languages.
        if(!in_array(Yii::$app->language, $availableLanguages))
        {
            Yii::$app->language = Yii::$app->getBaseLanguage(); // Reset the language.
            Yii::$app->request->cookies->remove('language');
            Yii::$app->session->remove('language');
            throw new \yii\base\ForbiddenHttpException('Language is not supported! Please refresh this page.'); 
        }
        
        //Set the Content-Language HTTP header field to the current language.
        Yii::$app->response->headers->add('Content-Language', Yii::$app->language);
    }
}
