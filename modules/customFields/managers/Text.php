<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

use \Yii;

/** Manages a CustomField of type text.*/
class Text extends Varchar
{   
    /** 
     * @inheritdoc 
     * */
    protected function renderInputInternal()
    {
        $input = [];
        
        if($this->field->size)
        {
            $size = $this->field->size > $this->maxInputSize ? $this->maxInputSize : $this->field->size ;
            // The maximum width is 98%
            $width = ceil($size * 98 / $this->maxInputSize);
        }
        else
        {
             $width = 98;
        }
        
        // If field size is under or equal to the maximum input size we use text input.
        if($this->field->size <= $this->maxInputSize && $this->field->size > 0)
        {
            $input['type'] = 'text';
            $input['class'] = 'shortText';
            $input['maxlength'] = $this->field->size;
            $input['style'] = 'width:'.$width.'%;';
        }
        // If field size is greater than 128 or 0 (infinite) we use textarea.
        else
        {
            $input['type'] = 'textarea';
            $input['class'] = 'longText';
            $input['rows'] = $this->field->size ? (ceil($this->field->size / 100) > 30 ? 30 : ceil($this->field->size / 100)) : 6;
            $input['style'] = 'width:'.$width.'%;';
        }
        
        // Description is completed if a field size is provided
        $description = $this->field->description;
        if($this->field->size)
        {
            $description .= '<br />'.Yii::t(__CLASS__, 'This field is limited to {fieldSize} characters', ['fieldSize' => $this->field->size]);
        }
        $input['hint'] = $description;
        
        $input[] = $this->getCharacterCounter();
        
        return $input;
    }
}
