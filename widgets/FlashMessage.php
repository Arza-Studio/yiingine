<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \Yii;

/**
 * A widget that displays site flash messages.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class FlashMessage extends \yii\base\Widget
{           
    /** @var string constant for success flash messages. */
    const SUCCESS = 'alert-success';

    /** @var string constant for info flash messages. */
    const INFO = 'alert-info';

    /** @var string constant for warning flash messages. */
    const WARNING = 'alert-warning';

    /** @var string constant for danger (error) flash messages. */
    const DANGER = 'alert-danger';
  
    /** @var integer the number of instances of this widget.*/
    static $instancesToSlideUp = 0;
    
    /** @var stringthe message type. */
    public $type = self::INFO;

    /** @var string the message to display. */
    public $message = '';
    
    /** @var string the template used to render the buttons. */
    public $template = '{toggle}{close}';
    
    /** @var array the buttons. */
    public $buttons = [];

    /** @var boolean set the delay for sliding up the message or false if no delay should be used. */
    public $slideUp = 3000;

    /** @var array html options. */
    public $options = [];
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        FlashMessageAsset::register($this->view);
        
        if($this->slideUp !== false)
        {
            self::$instancesToSlideUp++;
        }
        
        return $this->render('flashMessage');
    }
    
    /** 
     * Displays all flash messages.
     * @return string the flash messages.
     * */
    public static function display()
    {
        $result = '';
        
        foreach(Yii::$app->session->getAllFlashes(true) as $type => $flashes)
        {
            if(!in_array($type, [static::SUCCESS, static::INFO, static::WARNING, static::DANGER]))
            {
                continue; // Only display flash messages from this widget.
            }
            
            $processed = []; // List of processed messages, used to eliminate duplicates.
            
            if(!is_array($flashes))
            {
                $flashes = [$flashes];
            }
            
            foreach($flashes as $flash)
            {
                $params = is_array($flash) ? $flash : ['message' => $flash];
                if(in_array($params['message'], $processed))
                {
                    continue; // Skip this message, it's a duplicate.
                }
                $params['type'] = $type;
        
                $result .= self::widget($params);
                $processed[] = $params['message'];
            }
        }
        
        return $result;
    }
}


/** 
 * AssetBundle for the Overlay widget.
 * */
class FlashMessageAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/widgets/assets/flashMessage';
    
    /** @inheritoc */
    public $css = ['flashMessage.css'];
        
    /** @inheritdoc */
    public $depends = ['yii\web\JqueryAsset'];
}
