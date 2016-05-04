<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

/** 
 * Manages a CustomField of type color.
 * */
class Color extends Base
{                    
    /** 
     * @inheritdoc 
     * */
    protected function renderInputInternal()
    {
        return [
            'type' => 'text'
        ];
    }
    
    /**
     * @inheritdoc
     * */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [$this->getAttribute(), 'string', 'is' => 6],
            [$this->getAttribute(), 'match' , 'pattern' => '/^[a-f0-9]{6}+$/u']
        ]);
    }
}
