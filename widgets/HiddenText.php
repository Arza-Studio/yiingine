<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \Yii;

/**
 * Protects HTML text in a page by encrypting it and requesting it decryption by the server.
 */
class HiddenText extends \yii\base\Widget
{    
    /** @var string the text to encrypt. */
    public $text;
    
    /** @var string the message. */
    public $message;
    
    /** @var string the loading html. */
    public $loader;
    
    /** @var array the html attributes. */
    public $options;
    
    /** @var mixed
     *  Used if we need to display the hidden text for a particular hostname or IP address.
     *  Those exceptions are listed in an array.
     *  If the exceptions value is false (default), no exceptions are allowed.
     */
    public $exceptions = false;
    
    /** 
     * @return array the list of actions used by this widget.
     * */
    public static function actions()
    {
        return [
            'hiddenText.show' => ['class' => '\yiingine\widgets\HiddenTextShowAction']
        ];    
    }
    
    /**
    * @inheritdoc
    */
    public function run()
    {
        // Exceptions management
        // If exceptions var is an non-empty array
        if(is_array($this->exceptions) && !empty($this->exceptions))
        {
            // Check if there is a configEntry named HiddenText_exceptions
            // In this case this one overide the exceptions set in the source code
            $checkedExceptions = (isset(Yii::$app->params['HiddenText_exceptions'])) ? explode(',', Yii::$app->params['HiddenText_exceptions']) : $this->exceptions;
            
            // Exceptions can be specified using host names, so they need to be translated to IP adresses.
            foreach($checkedExceptions as $key => $exception)
            {
                // If the exception is already an IP.
                if(preg_match('^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9‌​]{2}|2[0-4][0-9]|25[0-5])$^', $exception))
                {
                    continue; // Already an IP so skip it.
                }
                
                // Attempt to retrieve the exception's IP from cache (DNS queries are slow).
                if(($ip = Yii::$app->cache->get($exception.'_IP')) === false)
                {
                    // The exception was not in cache.
                    $ip = gethostbyname($exception);
                    
                    // Cached value is valid for a week.
                    Yii::$app->cache->set($exception.'_IP', $ip, 60 * 60 * 24 * 7);
                }
                
                $checkedExceptions[$key] = $ip; // Replace the host name with it's IP.
            }
            
            // If the userHostAddress is in the exceptions the text is returned without hiding.
            if(in_array(Yii::$app->request->userIP, $checkedExceptions))
            {
                echo $this->text;
                return;
            }
        }

        if(!isset($this->message)) // If no message was set.
        {
            $this->message = Yii::t(__CLASS__, 'View');
        }
        
        HiddenTextAsset::register($this->view);
        
        return $this->render('hiddenText', [
            'hiddenText' => self::hide($this->text),
            'options' => $this->options
        ]);
    }
    
    /**
     * Hides a text by returning a hash value and keeping the text as a session variable
     * for retrieval through the API.
     * @param string $text, the text to hide.
     * @return string the key to the text.
     * */
    public static function hide($text)
    {
        //If no text table has been generated yet.
        if(!$table = Yii::$app->session['hiddenTextTable'])
        {
            $table = [];
        }
        
        //Store the text in the hidden text table with its hash as a key.
        $md5 = md5($text);
        $table[$md5] = $text;
        Yii::$app->session['hiddenTextTable'] = $table;
        
        return $md5;
    }
    
    /**
     * Shows text that has been hidden using the hide method.
     * @param string $key the key to the hidden text.
     * @return  mixed the hidden text or false if it was not found.
     * */
    public static function show($key)
    {
        if(!$table = Yii::$app->session['hiddenTextTable'])
        {
            return false; // Nothing is hidden;
        }
        
        return Yii::$app->session['hiddenTextTable'][$key];
    }
}

/** 
 * An action to allow the displaying of text hidden using the HiddenText widget. 
 * Useful for hiding sensitive data to bots such as telephone numbers or e-mails.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 * */
class HiddenTextShowAction extends \yii\base\Action
{
    /** Runs the action.
     * @param string $key the key of the hidden content. */
    public function run($key)
    {    
        // Retrieve the hidden text.
        if(($text = HiddenText::show($key)) === false)
        {
            throw new \yii\web\NotFoundHttpException();
        } 
        
        echo $text;
    }
}

/**
 * The asset bundle for the HiddenText widget.
 * */
class HiddenTextAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/widgets/assets/';
    
    /** @inheritdoc */
    public $js = ['hiddenText/hiddenText.js'];
}
