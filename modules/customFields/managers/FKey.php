<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

use \Yii;

/** 
 * Manages a CustomField of type fkey.
 * */
class FKey extends BaseRelational
{                                
    /** 
     * @inheritdoc
     * */
    protected function renderInputInternal()
    {
        $configuration = $this->getField()->getConfigurationArray();
        
        $class = $configuration['modelClass'];
        $models = $class::model()->findAll($configuration['queryConditions']); // Find all models that match the parameters in configuration.
        foreach($models as $m) // SEE BUG #797 !!!
        {
            $m->refresh();
        }
        
        $listData = [];
        
        if(isset($models[0])) // If at least one model was found.
        {
            // If the field targeted by the foreign key has translations.
            if(array_key_exists($configuration['attribute'].'_tid', $models[0]->metaData->columns))
            {
                foreach($models as $m) //Translate each label.
                {
                    if($m instanceof \yiingine\db\ViewableInterface)
                    {
                        $listData[$m->id] = $m->getTitle();
                    }
                    else
                    {
                        $listData[$m->id] = strip_tags($m->{$configuration['attribute']});
                    }
                }
            }
            else
            {
                foreach($models as $m)
                {
                    if($m instanceof \yiingine\db\ViewableInterface)
                    {
                        $listData[$m->id] = $m->getTitle();
                    }
                    else
                    {
                        $listData[$m->id] = strip_tags($m->{$configuration['attribute']});
                    }
                }
            }
        }
        // Else listData will be empty.
        
        return [
            'type' => 'dropdownlist',
            'items' => $listData,
            'prompt' => [$this->field->required ? null :0 => $this->field->required ? Yii::t('generic', 'Select an item'): Yii::t('generic', 'None')]
        ];
    }
    
    /**
     * @inheritdoc
     * */
    public function rules()
    {
        $configuration = $this->getField()->getConfigurationArray();
    
        return array_merge(parent::rules(), [
            [$this->getAttribute(), 'integer', 'min' => 0],
            [$this->getAttribute(), 'exist',
                'className' => $configuration['modelClass'],
                'attributeName' => 'id',
                'extensions' => $this->getExtensions(),
                'queryConditions' => isset($configuration['queryConditions']) ? array('condition' => $configuration['queryConditions']): ''
            ]
        ]);
    }
    
    /**
     * @inheritdoc
     * */
    protected function getRelations()
    {
        $configuration = $this->getField()->getConfigurationArray();
    
        // Create the relation name by removing the id suffix.
        if(strpos($this->getAttribute(), '_id') !== false)
        {
            $name = str_replace('_id', '', $this->getAttribute());
        }
        else // Or adding _relations if there is no _id suffix.
        {
            $name = $this->getAttribute().'_relation';
        }
    
        // Create a relation based on this field.
        return [$name => $this->owner->hasOne($configuration['modelClass'], ['id' => $this->getAttribute()])];
    }
}
