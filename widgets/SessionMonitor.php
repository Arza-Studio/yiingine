<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \Yii;
use \yii\helpers\Html;

/**
 * A widget that warns the user if his session has expired or is about to expire and that
 * keeps it alive if the user is using a form.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class SessionMonitor extends \yii\base\Widget
{        
    /** @return array the list of actions used by this widget.*/
    public static function actions()
    {
        return [
            'sessionMonitor.ping' => ['class' => '\yiingine\widgets\SessionMonitorPingAction']
        ];    
    }
    
    /**
    * @inheritdoc
    */
    public function run()
    {           
        if(Yii::$app->user->authTimeout === null) // If the session will never timeout.
        {
            return; // Do not display this widget.
        }
        
        if(Yii::$app->request->isAjax) // If this is an ajax request.
        {
            return; // Do not display this widget.
        }
        
        $result = '';
        
        $authTimeout = Yii::$app->user->authTimeout;
        $clickHereMessage = Yii::t(__CLASS__, 'Renew session');
        
        $result .= Html::tag('div', FlashMessage::widget([
            'id' => 'sessionExpirationWarning',
            'type' => FlashMessage::WARNING,
            'message' => Yii::t(__CLASS__, 'Your session is about to expire.'),
            'template' => '{renew}',
            'buttons' => [
                '{renew}' => Html::a($clickHereMessage, '#', [
                    'title' => $clickHereMessage,
                    'class' => 'alert-link',
                    'onclick' => 'yii.sessionMonitor.sendPing();'
            ])],
            'slideUp' => false,
        ]), ['style' => 'display:none;']);
        
        if(Yii::$app->user->getIdentity()->superuser) // Allow superusers to log back in.
        {
            $template = '{logBackInThisWindow}&nbsp{logBackinNewWindow}';
            $logBackThisWindowMessage = Yii::t(__CLASS__, 'Log back in this window');
            $logBackNewWindowMessage = Yii::t(__CLASS__, 'Log back in a new window');
            $buttons = [
                '{logBackInThisWindow}' => Html::a($logBackThisWindowMessage, '#', [
                    'class' => 'alert-link',
                    'title' => $logBackThisWindowMessage,
                    'onclick' => 'window.location.reload()'
                ]),
                '{logBackinNewWindow}' => Html::a($logBackNewWindowMessage, \yii\helpers\Url::current(), [
                    'class' => 'alert-link',
                    'title' => $logBackNewWindowMessage,
                    'target' => '_blank'
                ]),
            ];
        }
        else // The user is not a superuser.
        {
            $buttons = [];
            $template = '';
        }
        
        $result .= Html::tag('div', FlashMessage::widget([
            'id' => 'sessionExpired',
            'type' => FlashMessage::DANGER,
            'message' => Yii::t(__CLASS__, 'Your session has expired !'),
            'buttons' => $buttons,
            'template' => $template,
            'slideUp' => false,
        ]), ['style' => 'display:none;']);
        
        $host = Yii::$app->request->hostInfo.Yii::$app->request->baseUrl;
        $pingUrl = \yii\helpers\Url::to(['/api/sessionMonitor.ping/', 'hash' => '']);
        $this->view->registerJs(<<<EOD
        
            /* An object that manages session timeouts.
             * @param integer authTimeout the timeout value in seconds for the session */
            yii.sessionMonitor = function(authTimeout)
            {                
                // Displays a banner to inform the user that his session is about to expire.
                this.displayExpirationWarning = function()
                {
                    $("#sessionExpirationWarning").prependTo($("#flash-messages")).show();
                    $(window).trigger("resize");
                    $("html, body").animate({scrollTop:0});
                    clearInterval(sessionExpirationCheck);
                };
                
                // Displays a banner to inform the user that his session has expired.
                this.displayExpired = function()
                {
                    if($("#sessionExpirationWarning:visible"))
                    {
                        $("#sessionExpirationWarning").hide();
                    }
                    $(window).trigger("resize");
                    $("#sessionExpired").prependTo($("#flash-messages")).show();
                    $(window).trigger("resize");
                    $("html, body").animate({scrollTop:0});
                    
                    clearInterval(sessionExpirationCheck);
                    clearInterval(sessionExpiredCheck);
                };
                
                // This function sends a ping to the website to keep the current session alive.
                this.sendPing = function()
                {                
                    yii.sessionMonitor.lastPing = new Date().getTime();
                
                    // Keep the session open.
                    // A random number is appended to prevent browsers or servers from caching the request.
                    $.get("$pingUrl" + Math.floor(Math.random() * 1000000), {}, function() 
                    {
                        yii.sessionMonitor.resetTimers();
                        yii.sessionMonitor.removeWarnings();
                        
                    }).fail(function()
                    {
                        // The ping request was blocked, this most likely means that the session has expired.
                        yii.sessionMonitor.displayExpired();
                    });
                }
                
                // This function removes warnings.
                this.removeWarnings = function()
                {
                    $("#sessionExpired").fadeOut(400, function()
                    { 
                        $(window).trigger("resize");
                    });
                
                    $("#sessionExpirationWarning").fadeOut(400, function()
                    { 
                        $(window).trigger("resize");
                    });
                }
                
                // This function resets the session monitor timers.
                this.resetTimers = function()
                {
                    window.clearInterval(this.sessionExpiredCheck);
                    // Display a session expired warning after the timeout interval.
                    this.sessionExpiredCheck = setInterval(this.displayExpired, this.authTimeout * 1000);
                
                    window.clearInterval(this.sessionExpirationCheck);
                    /* Check for session expiration after the authTimeout delay + 1 second, so sending
                    the message will not reset the time.*/
                    // This event will display a warning to inform the user that his session has expired.
                    this.sessionExpirationCheck = setInterval(this.displayExpirationWarning, this.authTimeout * 750);
                
                    localStorage.setItem("$host.resetSessionMonitorTimers", this.lastPing);
                }
    
                 // Time of the last ping on the server minus one minute so the first ping is sent.
                this.lastPing = new Date().getTime();
                this.lastChange = this.lastPing;
                
                this.authTimeout = authTimeout;
                
                // Sends a ping to the admin at a definite interval.
                setInterval(function()
                { 
                    // If the server has not been informed of a change in the forms.
                    if(yii.sessionMonitor.lastChange > yii.sessionMonitor.lastPing)
                    {                    
                        yii.sessionMonitor.sendPing();
                    }
                }, 60000);
                
                jQuery(function()
                {
                       updateLastChange = function(){ yii.sessionMonitor.lastChange = new Date().getTime(); };
                    // Listen for changes on form input elements to send a ping.
                    $("input, textarea").click(updateLastChange).keyup(updateLastChange).change(updateLastChange);
                    
                    // Each time an ajax request is done, consider it a change on the page.
                    $(document).ajaxComplete(updateLastChange);
                
                    yii.sessionMonitor.resetTimers();
                    
                    // Adds a listener for messages from other windows passed through localStorage.
                    $(window).bind("storage", function(e)
                    {
                        // If the event was for another value or if we have already responded to this event.
                        if(localStorage.getItem("$host.resetSessionMonitorTimers") == null || localStorage.getItem("$host.resetSessionMonitorTimers") < yii.sessionMonitor.lastPing)
                        {
                            return;
                        }
                
                        yii.sessionMonitor.removeWarnings();
                        yii.sessionMonitor.resetTimers();
                    });
                });
                
                return this;
            }($authTimeout);
EOD
        , \yii\web\View::POS_END);
        
        return $result;
    }
}

/** This action is used in conjuction with ajax to keep the current session open
 * while a user is working on a page.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> */
class SessionMonitorPingAction extends \yii\base\Action
{
    /** 
     * Runs the action.
     * @param integer $hash some random data that is added to the query to prevent 
     * the browser from caching the ajax request.
     * */
    public function run($hash = false)
    {    
        if(!Yii::$app->request->isAjax)
        {
            throw new \yii\web\BadRequestHttpException();
        }
        
        // Important, this request should never be cached.
        Yii::$app->response->headers->set('Pragma', 'no-cache');
        
        return '';
    }
}
