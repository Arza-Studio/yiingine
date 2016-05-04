<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\adminSiteToolbar\widgets;

use \Yii;

/**
 * A button for the AdminSiteToolbar.
 * */
class Button extends \yii\base\Widget
{    
    /** @var string the button tag. */
    public $tag = 'button';
    
    /** @var string the css class for the button. */
    public $class = 'btn adminSiteToolbarBtn';
    
    /** @var string the content of the button. */
    public $content = '';
    
    /** @var array the html options for the button. */
    public $options = [];
    
    /** @var boolean if the button should be visible. */
    public $visible = true;
    
    /**
     * @inheritdoc
     * */
    public function run()
    {
        if(!$this->visible)
        {
            return;
        }
        
        $this->options['class'] = $this->class;
        
        return \yii\helpers\Html::tag($this->tag, $this->content, $this->options);
    }
}
