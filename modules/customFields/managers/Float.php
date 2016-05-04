<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

/** Manages a CustomField of type float.*/
class Float extends Integer
{            
    /**
     * @inheritdoc
     * */
    public function rules()
    {
        $configuration = $this->getField()->getConfigurationArray();
        return array_merge(parent::rules(), [
            [$this->getAttribute(), 'double', 
                'min' => isset($configuration['minimum']) ? $configuration['minimum'] : null,
                'max' => isset($configuration['maximum']) ? $configuration['maximum'] : null
            ],
        ]);
    }
}
