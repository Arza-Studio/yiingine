<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

/** 
 * Manages a CustomField of type boolean.
 * */
class Boolean extends Base
{                
    /** 
     * @inheritdoc 
     * */
    protected function renderInputInternal()
    {
        return [
            'type' => 'checkbox', 
            'layout'=> "{input} {label}\n{hint}\n{error}"
        ];
    }
    
    /**
     * @inheritdoc
     * */
    public function rules()
    {
        return array_merge(parent::rules(), [[$this->getAttribute(), 'boolean']]);
    }
}
