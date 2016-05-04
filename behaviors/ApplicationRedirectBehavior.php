<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
namespace yiingine\behaviors;

use \Yii;

/** This behavior redirects requests made to alternate domains to
 * the proper domain.
 */
class ApplicationRedirectBehavior extends \yii\base\Object
{
    /** 
     * @inheritdoc
     * */
    public function init()
    {
        // Register this object to handle the before request event.
        Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, [$this, 'beforeRequest']);
        
        parent::init();
    }
 
    /**
     * Redirect requests to the main domain or use a redirection map 
     * if it is defined.
     * @param Event $event the event that triggered the handler.
     */
    public function beforeRequest($event)
    {        
        // If the 'app.main_domain' configuration entry is not set.
        if(!Yii::$app->getParameter('app.main_domain', false))
        {
            /* It has been inadvertantly deleted, create a bogus one to prevent site lockup.
             * example.com is used as using the name provided by Yii::$app->request->serverName could pose a security risk.*/
            (new \yiingine\models\ConfigEntry(['name' => 'app.main_domain', 'value' => 'example.com']))->save();
            
            throw new \yii\base\Exception('No main domain name configured, setting "example.com" as main domain, please configure a new main domain.');
        }
        // If the request's domain matches the main domain.
        else if(Yii::$app->request->serverName == Yii::$app->params['app.main_domain'])
        {
            return; //Nothing to do.   
        }   
        
        $request = Yii::$app->request;
        
        /*The domains can be redirected using two mechanisms:
         * 1) Main domain and alternate domains
         *     The simpler technique where is a domain is not found to match
         *     the main domain, it is redirected.
         * 2) Redirection map
         *     The more complicated but more capable technique.
         *     a string in the form of
         *     domain1:redirection1,domain2:redirection2,... is set in the configuration.
         *     If a domain is found to match one in the list, it is redirected to is
         *     corresponding redirection.
         *     
         * Regardless of the techniques, the main_domain configuration entry must always
         * be set.*/
        
        #REDIRECTION USING ALTERNATE DOMAINS

        // If there is an alternate domain configuration entry.
        if(Yii::$app->getParameter('app.alternate_domains'))
        {            
            // If the domain the request was made with is part of the alternate domains.
            if(in_array($request->serverName, explode(',', Yii::$app->params['app.alternate_domains'])))
            {
                $hostInfo = str_replace($request->serverName, Yii::$app->params['app.main_domain'], $request->hostInfo);

                // Redirect to the same url but with the main domain instead.
                Yii::$app->response->headers->add('Location', $hostInfo.$request->url);
                
                throw new \yii\web\HttpException(301); // Permanent redirect.
            }
            
            return;
        }
        
        #REDIRECTION USING A REDIRECTION MAP
        
        // If there is an domain_redirection_map configuration entry.
        if($map = Yii::$app->getParameter('app.domain_redirection_map'))
        {
            $map = str_replace(' ', '', $map); //Get rid of all spaces.
            
            // Iterate through all redirection entries.
            foreach(explode(',', $map) as $entries)
            {
                // Split an entry into its two parts.
                $entries = explode(':', $entries);
                
                // If there is more than two components in the entry.
                if(count($entries) != 2)
                {
                    throw new \yii\base\Exception('"app.domain_redirection_map" is malformed.');
                }
                //If the request's domain matches a redirection rule.
                else if($request->serverName == $entries[0])
                {
                    $hostInfo = str_replace($request->serverName, $entries[1], $request->hostInfo);
                    
                    // Redirect to the same url but with the main domain instead.
                    Yii::$app->response->headers->add('Location', $hostInfo.$request->url);
                    
                    throw new \yii\web\HttpException(301); // Permanent redirect.
                } 
            }
            
            /* If we arrive here, this means the request domain was deemed to
             * be acceptable.*/
        }        
    }
}
