<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\controllers;

use \Yii as Yii;
use \yii\helpers\Url;

/**
* The main controller for the front-end of the yiingine.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class SiteController extends \yiingine\web\SiteController
{    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return array_merge([
                // Page action renders "static" pages stored under 'views/site/pages'.
                'page' => [
                    'class' => '\yii\web\ViewAction',
                    /*If the current languages is different than the sourceLanguage.
                     * static views are fetched in their laguages subfolder.*/
                    'viewPrefix' => \Yii::$app->language != \Yii::$app->sourceLanguage && file_exists(\Yii::$app->basePath.'/views/site/pages/'.\Yii::$app->language) ? 
                        'pages'.\Yii::$app->language:
                        'pages',
                ],
                'error' => [
                    'class' => '\yii\web\ErrorAction',
                    'view' => '//system/error'
                ]
            ],
            // Captcha action renders the CAPTCHA image displayed on the problem report form.
            \yiingine\widgets\Captcha::actions()
        );
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex()
    {
        foreach(Yii::$app->urlManager->rules as $rule)
        {
            if($rule->name == '') // If a rule exists for the index route.
            {
                $this->redirect(['/']);
            }
        }
        
        return $this->render('index'); // Render index by default.}
    }
    
    /**
    * This is the action to handle out-of-date browsers.
    */
    public function actionUpdateClient()
    {
        if(Yii::$app->request->post('ignore')) //If the request was a POST with ignore set.
        {
            /*The user has opted to ignore the update browser warning.
             * Set a cookie so he does not get bothered until the next time
             * he opens his browser and access the site.*/
            Yii::$app->response->cookies->add(new \yii\web\Cookie(['name'=> 'ignoreUpdateClient', 'value'=> '1']));
            return $this->goHome();
        }
        
        return $this->renderPartial('updateClient'); // Render the updateBrowser view.
    }
    
    /** 
     * Get the last modification date/time of the database.
     * @return DateTime the last modification date/time or null if it could not be found. 
     * */
    private function _getLastModificationDateTime()
    {
        /*Try to deduce the last modification by querying the table of each modules.
         * a table is assumed to belong to a module because it will either be named
         * after it of will contain its name as a prefix. 
         * 
         * Note: models from a module cannot be fetched because they may have
         * dependencies we cannot resolve here.*/
        
        $lastmod = null; // Will contain the latest modification date.
        
        foreach(Yii::$app->db->schema->tableSchemas as $table)
        {
            // If the table has a column named ts_updt.
            if($col = $table->getColumn('ts_updt'))
            {            
                // Query the most recent timestamp from the table.
                $result = (new \yii\db\Query())->select(['ts_updt'])->from($table->name)->orderBy('ts_updt DESC')->limit(1)->one();
                
                if(isset($result['ts_updt'])) //If the query returned something.
                {
                    $mod = new \DateTime($result['ts_updt']);
                    
                    /*If lastmod has not been initialized yet of its date
                     * is later than the current date.*/
                    if($lastmod === null || $lastmod < $mod)
                    {
                        $lastmod = $mod;
                    }
                }
            }
        }
        
        return $lastmod;
    }
    
    /**
     * Renders the siteMapIndex for this site.
     */
    public function actionSitemapIndex()
    {
        /* Every module gets a default/sitemap action whether or not it is 
         * implemented, it will be the task of each module to respond accordingly
         * (maybe with a 404)*/
        
        $ultimateModification = null;
        
        // For each module in the site.
        foreach(Yii::$app->modules as $k => $v)
        {
            $map = [];
            $module = Yii::$app->getModule($k); // Gets an instance of the module.
            
            //If the module is an instance of \yiingine\base\Module.
            if($module instanceof \yiingine\base\Module)
            {
                if($module->moduleMapRoute === false)
                {
                    continue;
                }
                
                // Let the module set its own map route. The url generated does not include a language.
                $map['url'] = Url::to(['/'.$k.$module->moduleMapRoute], true);
            }
            else
            {
                // Set the map route to a default value.  The url generated does not include a language.
                $map['url'] = Url::to(['/'.$k.'/default/module-map'], true);
            }
            
            $maps[$k] = $map;
        }
        
        $maps['site'] = [ //The map for the site.
            'url' => Url::to(['/site/sitemap'], true)
        ];
        
        $lastmod = $this->_getLastModificationDateTime();
            
        if($lastmod !== null) //If a last modification date has been found.
        {
            $maps['site']['lastmod'] = $lastmod->format(\DateTime::W3C);
        }
        
        // Do not return a string here because an XML formatter is being used.
        Yii::$app->response->content = $this->renderPartial('sitemapIndex', ['maps' => $maps]);
    }
    
    /**
     * Renders the sitemap known to this controller. Does not include the modules's sitemaps.
     */
    public function actionSitemap()
    {           
        $pages = [
            'index' => [
                'loc' => ['/'],
                'priority' => '1.0',
            ]
        ];
        
        $lastmod = $this->_getLastModificationDateTime();
        
        if($lastmod !== null) //If a last modification date was found.
        {
            $pages['index']['lastmod'] = $lastmod->format(\DateTime::W3C);
        }
        
        // The rest will contain sitemap entries for static pages.
        if(is_dir(Yii::getAlias('@app/views/site/pages')))
        {    
            // For each file in the static pages directory.
            foreach(scandir(Yii::getAlias('@app/views/site/pages')) as $file)
            {
                if(strpos($file, '_') === 0) //If the page starts with "_".
                {
                    continue; // It is a partial view, do not add it to the sitemap.
                }
                
                $pos = strpos($file, '.php'); // The position of ".php" within the file name.
                
                // If the file name ends with ".php", this means it is a page and should be added to the sitemap.
                if($pos !== false && $pos == strlen($file) - 4)
                {
                    $file = str_ireplace('.php', '', $file); // Remove ".php".
                    // Create the page entry and add it to the $pages array.
                    $pages[] = [
                        'loc' => ['/site/page/'.$file],
                        'changefreq' => 'monthly',
                        ///TODO: lastmod should be the modification date of the file on the server.
                        ///TODO: does not account for pages that are in translated folders.
                    ];
                }   
            }
        }
        
        // Do not return a string here because an XML formatter is being used.
        Yii::$app->response->content = $this->renderPartial('sitemap', ['pages' => $pages]);
    }
    
    /**
    * Renders the robots.txt.
    */
    public function actionRobots()
    {
        return $this->renderPartial('robots');
    }
    
    /** This action allows a user to send a problem report to a technical support email using
     * a prefilled form.
     * @param string $url the url that caused the problem.
     * @param string $referrer the url referrer of the problem page. 
     * @param string $method the HTTP method. 
     * @param integer $code the HTTP error code.
     * @param string $message the HTTP error message.
     * @param integer $screenHeight the height of the screen.
     * @param integer $screenWidth the width of the screen. */
    public function actionProblemReport($url = '', $referrer = '', $method = 'GET', $code = '', $message = '', $screenHeight = 0, $screenWidth = 0)
    { 
        // If error reporting has been disabled.
        if(Yii::$app->getParameter('yiingine.error_reporting.enabled', '1') === '0')
        {
            throw new \yii\web\ForbiddenHttpException();
        }
        
        $email = Yii::$app->getParameter('yiingine.error_reporting.email', false);
        
        if(!$email)
        {
            throw new \yii\web\HttpException(500, 'Missing error_reporting.email configuration entry');
        }
        else if(!\yii\base\DynamicModel::validateData(['email' => $email], [['email', 'email']])->validate())
        {
            throw new \yii\web\HttpException(500, 'error_reporting.email configuration entry is not a valid email.');
        }
        
        $model = new \yiingine\models\ProblemReport();
        
        if($url === '') // If no url was specified.
        {
            $model->url = \yii\helpers\Url::home(true);
        }
        else
        {
            /* The reason why {{ is changed to / is that for security reason, it is not a 
             * good idea to pass forward slashes in urls. See
             * http://stackoverflow.com/questions/3235219/urlencoded-forward-slash-is-breaking-url
             * for more information.*/
            $model->url = Yii::$app->request->hostInfo.urldecode($url);
        }
        
        // If the error was triggered by clicking on a link.
        $model->referrer = urldecode($referrer);
        
        $model->message = urldecode($message);
        $model->method = $method;
        $model->code = $code;
        $model->screenHeight = $screenHeight;
        $model->screenWidth = $screenWidth;
        
        if(!Yii::$app->user->isGuest)
        {
            $model->email = Yii::$app->user->getIdentity()->email; // Prefill the email.
            $model->userId = Yii::$app->user->getIdentity()->id;
        }
        else
        {
            $model->userId = '0';
        }
                
        // If this was a post request containing the problem report.
        if(Yii::$app->request->post('ProblemReport'))
        {
            $model->attributes = Yii::$app->request->post('ProblemReport');
            
            if($model->sent) // If the report has already been sent.
            {
                // Inform the user that the message was already sent.
                Yii::$app->session->setFlash(\yiingine\widgets\FlashMessage::SUCCESS, Yii::t(__CLASS__, 'Problem report already sent.'));
            }
            else if($model->validate()) // If the form was correctly filled out.
            {                                
                $message = Yii::$app->mailer->compose('@yiingine/views/site/problemReportEmail', ['model' => $model]);

                $message->setTo($email);
                
                if($model->copy) // If the user wanted a copy of the report.
                {
                    $message->setCc($model->email);
                }
                
                $message->setFrom(Yii::$app->getParameter('app.system_email', 'system@notset.com'));
                $message->setSubject(Yii::t(__CLASS__, '{app} Problem Report', array('{app}' => Yii::$app->name)));
                            
                if(!$message->send()) //If the message did not send.
                {
                    throw new \yii\web\ServerErrorHttpException(Yii::t(__CLASS__, 'Could not send report, please try again later.'));
                }
                
                $model->sent = true; // Report has been sent.
                
                // Inform the user that the message was sucessfully sent.
                Yii::$app->session->setFlash(\yiingine\widgets\FlashMessage::SUCCESS, Yii::t(__CLASS__, 'Problem report sucessfully sent.'));
            }
        }
        
        return $this->render('problemReport', ['model' => $model]);
    }
    
    /** 
     * This action displays a simple maintenance page. 
     * */
    public function actionMaintenance()
    {
        // Maintenance mode is not enabled.
        if(!Yii::$app->getParameter('app.emergency_maintenance_mode.enabled'))
        {
            throw new \yii\web\NotFoundHttpException();
        }
        
        return $this->renderPartial('maintenance');
    }
    
    /**
     * Renders the corpus CSS dynamically.
     */
    public function actionCorpus()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/css');
        $seconds = 10 * 3600 * 24; // Expires in 10 days.
        Yii::$app->response->headers->add('Expires', gmdate('D, d M Y H:i:s', time() + $seconds) . ' GMT');
        Yii::$app->response->headers->add('Pragma', 'cache');
        Yii::$app->response->headers->add('Cache-Control', 'max-age='.$seconds);
        
        Yii::$app->layout = false;
        
        return $this->render('corpus');
    }
}
