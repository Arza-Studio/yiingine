<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

/** Manages a CustomField of type integer.*/
class Integer extends Base
{    
    /** 
     * @inheritdoc
     */
    protected function renderInputInternal()
    {
        return [
            'type' => 'text',
            'size' => $this->field->size > 60 ? 60: $this->field->size, 
            'maxlength' => $this->field->size
        ];
    }
    
    /**
     * @inheritdoc
     * */
    public function rules()
    {
        $configuration = $this->getField()->getConfigurationArray();
        
        return array_merge(parent::rules(), [
            [$this->getAttribute(), 'integer',
                'min' => isset($configuration['minimum']) ? $configuration['minimum'] : null,
                'max' => isset($configuration['maximum']) ? $configuration['maximum'] : null
            ],
            // Validate that the number is no above the size of the column.
            [$this->getAttribute(), 'string', 'max' => $this->field->size]
        ]);
    }
}
