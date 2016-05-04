<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

use \Yii;

/** Manages a CustomField of type enum.*/
class Enum extends Base
{                    
    /**
     * @inheritdoc
     */
    public function render($model, $return = false)
    {
        $labels = $this->getLabels();
        $value = $model->{$this->getAttribute()};
        
        $result = (isset($labels[$value])) ? $labels[$value] : '' ;
        
        if($return)
        {
            return $result;
        }
        
        echo $result;
    }
    
        /**
     * @inheritdoc
     */
    public function getLabels()
    {
        $configuration = $this->getField()->getConfigurationArray();
        return $configuration['data'];
    }
    
    /**
     * @inheritdoc
     */
    protected function renderInputInternal()
    {
        $configuration = $this->getField()->getConfigurationArray();
        
        $options = [];
        if(!$this->field->default) // If field does not have a default.
        {
            $options['prompt'] = Yii::t('generic', 'Select an item');
        }
        if(!$this->field->required) // If field is not required.
        {
            $options['prompt'] = Yii::t('generic', 'None');
        }
        
        return array_merge($options, [
            'type' => 'dropdownlist',
            'items' => $configuration['data']
        ]);
    }
    
    /**
     * @inheritdoc
     * */
    public function rules()
    {
        $configuration = $this->getField()->getConfigurationArray();
        
        return array_merge(parent::rules(), [
            [$this->getAttribute(), 'in', 'range' => array_keys($configuration['data'])]
        ]);
    }
}
