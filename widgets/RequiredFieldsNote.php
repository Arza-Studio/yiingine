<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

/**
 * A widget to inform the user what markers is used to indicate required fields.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 * */
class RequiredFieldsNote extends \yii\base\Widget
{
    /**
     * @var string the tag used for the note.
     * */
    public $tag = 'p';
    
    /**
     * @var array the html options for the note.
     * */
    public $options = ['class' => 'text-info', 'style' => 'text-align: right;'];
    
    /**
     * @var string the marker. 
     * */
    public $marker = '<span class="required-field-marker">*</span>';
    
    /**
     * @inheritdoc
     * */
    public function run()
    {
        return $this->render('requiredFieldsNote');
    }
}
