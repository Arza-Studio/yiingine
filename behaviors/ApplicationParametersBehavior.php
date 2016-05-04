<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\behaviors;

use \Yii;

/**
 * An event handler that loads additional config parameters that cannot be statically 
 * written in the configuration files.
 */
class ApplicationParametersBehavior extends \yii\base\Object
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
     * Load configuration from the database or generate it at runtime.
     * @param Event $event the event that triggered the handler.
     */
    public function beforeRequest($event)
    {
           $app = $event->sender;
        $session = $app->session; // Get the session component.
        
           ### SET LANGUAGE #####################
           //Language is set first because the configuration will depend upon it.
                      
           if($session['language']) // If the language has been set in session.
           {
               $app->language = $session['language']; // Set it.
           }
           // If there is a cookie giving the language.
           else if($app->request->cookies->has('language'))
           {
               // Set it as the active language.
               $language = $app->request->cookies->get('language')->value;
               $app->language = $language;
               $session['language'] = $language;
               //Note: If the cookie points to a language that is not supported, an error will be thrown later.
           }
           // If not it will be detected afterwards.
        
        ### PARAMETERS INITIALISATION #################
        
        /*Parameters are first fetched from the config file and are then supplemented
         * with those stored in the database. This lets entries in the database override
         * those in the config file.*/
        
        // Attemps to fetch the params array from cache.
        if(!$params = $app->cache->get('configuration_'.$app->language))
        {        
            $params = [];
            
            foreach(\yiingine\models\ConfigEntry::find()->all() as $entry)
            {
                // Some entries can not be overriden from the database for security reasons.
                if(in_array($entry->name, [
                    'app.special_users',
                    'enable_auth_management',
                    'app.log_active_record_changes'
                ]))
                {
                    continue; //Skip it.
                }
                
                //Translate the application parameter.
                $params[$entry->name] = $entry->translatable ? $entry->value: $entry->getAttribute('value');
            }

            ### SPECIAL CONFIGURATION PROCESSING #################
            
            //If the incompatible_*_browsers config item is set. Eval it.
            foreach(array('app.incompatible_browsers', 'app.incompatible_admin_clients', 'incompatible_api_clients') as $entry)
            {
                if(isset($params[$entry]))
                {
                    $list = eval('return array('.stripslashes($params[$entry]).');');
                    if($list !== false) //If the eval worked.
                    { 
                        $params[$entry] = $list;
                    }
                    else //There was an error during the evaluation.
                    {
                        unset($params[$entry]); //Remove that entry.
                    }
                }
            }
            
            // Some parameters have to be converted to arrays.
            foreach(array('app.supported_languages', 'app.available_languages') as $name)
            {
                if(isset($params[$name]))
                {
                    $params[$name] = explode(',', str_replace(' ', '', $params[$name]));
                }
            }
            
            ### PARAMETERS VERIFICATION #################
            
            /* In order to avoid veryfying parameters during each request, merge the database
             * parameters with those in main/config so the verification can happen when the params
             * cache entry gets rebuilt. */
            $paramsToCheck = array_merge($app->params, $params);
            
            //Check if some languages in available_languages are not in supported_languages.
            $diff = array_diff($paramsToCheck['app.available_languages'], $paramsToCheck['app.supported_languages']);
            if(count(array_diff($paramsToCheck['app.available_languages'], $paramsToCheck['app.supported_languages'])))
            {
                //All available_languages must be part of the supported_languages.
                throw new \yii\base\Exception('The following languages are set but are not part of the supported languages: '.implode(',', $diff));
            }
            
            // Put $params in cache.
            $app->cache->set('configuration_'.$app->language, $params, 0, new \yiingine\caching\GroupCacheDependency(['ConfigEntry']));
        }
        
        ### DYNAMIC CONFIGURATION PROCESSING #################
        // ie: stuff that cannot be cached.
        
        // BROWSER DETECTION    
        $env = new \yiingine\libs\Browser(); // Creates a new Browser object.
        $env->Browser();
        
        // Gets the browser, replaces the spaces and make it lower char.
        $browser = mb_strtolower(str_replace(' ','_',$env->getBrowser()));
        
        // If the browser is IE.
        if($browser == 'internet_explorer')
        {
            /*Gets the version.*/
            $version = explode('.',$env->getVersion());
            /*Gets the major version.*/
            $browser = 'ie'.$version[0];
        }
        /* Gets the platform, replaces the spaces and make it lower char and
         * puts it in $params.*/
        $params['platform'] = mb_strtolower(str_replace(' ','_',$env->getPlatform()));
        $params['browser'] = 
        $params['isMobileBrowser'] = $env->isMobile(); //If the site is being browser with a mobile.
        
        // NEW USER DETECTION
        /*Checks if this is the first time the user enters thew site.*/
        if(!isset($session['first_loading']))
        {
            $params['first_loading'] = 1; //Sets the config item to indicate that.
            $session['first_loading'] = 1; //Save it as a session parameter.
        }
        else
        {
            $params['first_loading'] = 0; //This is not the first loading.
            $session['first_loading'] = 0; //Save it as a session parameter.
        }
        
        // APPLICATION PARAMETERS SAVING
        /* So they can be acessed from anywhere within the application. They get
         * merged with those present in the config file so parameters from the database
         * can override those in the config file.*/
        $app->params = array_merge($app->params, $params);
        
        // APPLICATION NAME
        /*If a configuration entry from the database overrides the default application name.*/
        if(isset($params['app.name']))
        {
             $app->name = $params['app.name'];
        }
    }
}
