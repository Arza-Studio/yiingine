<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\controllers\admin;

/**
* A controller that contains actions for debugging purposes.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class DebugController extends \yiingine\web\SiteController
{    
    /** Initialise the controller. Override of parent implementation to deny
     * access to this controller when the Yii is not in debug mode.*/
    public function init()
    {
        if(!YII_DEBUG) // Only accessible in debug mode.
        {
            throw new \yii\web\ForbiddenHttpException();
        }
        
        parent::init();
    }
    
    /**
     * Destroys the current session. Useful for testing.
     */
    public function actionDestroySession()
    {
        /*WARNING! The following is not good practice because we are changing
         * the server state with a GET request.*/
    
        \Yii::$app->session->destroy(); // Destroys the session.
        \Yii::$app->response->cookies->removeAll(); // Clear all cookies.
        
        $this->goHome(); // Redirect to the home page.
    }
    
    /**
     * Dumps the content of $_SESSION.
     * */
    public function actionDisplaySession()
    {
        dump($_SESSION);   
    }
    
    /**
     * Dump the content of $_Server.
     * */
    public function actionDisplayServer()
    {
        dump($_SERVER);
    }
    
    /** 
     * Displays the output of phpinfo().
     * */
    public function actionPhpInfo()
    {
        return phpInfo();
    }
    
    /** 
     * Flushes the cache.
     * */
    public function actionFlushCache()
    {
        /* This action should be only accessible using a POST, but since
         * the site may be unusable before the cache has been flushed, an
         * exception is made.*/
        
        \Yii::$app->clean();
        
        $this->goHome(); // Redirect to the home page.
    }
    
    /** Trigger an exception with the given code.
     * @param integer $error the HTTP error code.
     * @param string $message the HTTP error message.*/
    public function actionException($error, $message = 'This is a test')
    {
        throw new \yii\web\HttpException((int)$error, $message);
    }
}
