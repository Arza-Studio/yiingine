<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;


/** 
 * Manages a CustomField of type position.
 * */
class Position extends Base
{    
    /** @var string the name of the first related attribute for grouping. */
    public $relatedAttribute = '';
    
    /** @var string the name of the second related attribute for grouping. */
    public $relatedAttribute1 = '';
    
    /** @var string the name of the third related attribute for grouping. */
    public $relatedAttribute2 = '';
    
    /** @var string a php expression to generate an action params array for the position manager widger. */
    public $actionParams = '[]';
    
    /** 
     * @inheritdoc 
     * */
    public function getBehavior($model)
    {
        $this->setRelatedAttributes($model);
        
        $behavior = new \yiingine\behaviors\ActiveRecordOrderingBehavior();
        
        if($this->relatedAttribute) // If a related attribute has been defined.
        {
            // Use it for grouping.
            $behavior->groupingAttributes = [$this->relatedAttribute];
        }
        
        if($this->relatedAttribute1)
        {
            $behavior->groupingAttributes[] = $this->relatedAttribute1;
        }
        
        if($this->relatedAttribute2)
        {
            $behavior->groupingAttributes[] = $this->relatedAttribute2;
        }
        
        return $behavior;
    }
    
    /** @param CustomizableModel $model the model to which the position field belongs.
     * @return CDbCriteria a CDbCriteria thaf finds the last position position.*/
    public function getCriteriaForLastPosition($model)
    {
        $this->setRelatedAttributes($model);
        
        $criteria = new CDbCriteria();
        
        if($this->relatedAttribute)
        {
            $criteria->compare($this->relatedAttribute, $model->{$this->relatedAttribute});
        }
        
        if($this->relatedAttribute1)
        {
            $criteria->compare($this->relatedAttribute1, $model->{$this->relatedAttribute1});
        }
        
        if($this->relatedAttribute2)
        {
            $criteria->compare($this->relatedAttribute2, $model->{$this->relatedAttribute2});
        }
        
        return $criteria;
    }
    
    /** Sets the related attributes for particular model.
     * @param CustomizableModel the model to which the position field belongs */
    protected function setRelatedAttributes($model)
    {
    }
    
    /** 
     * @inheritdoc 
     * */
    protected function renderInputInternal()
    {
        $this->setRelatedAttributes($model);
        
        return array(
            'type' => 'app.components.widgets.admin.CPositionManager',
            'model' => $model,
            'relatedAttribute' => $this->relatedAttribute,
            'relatedValue' => $this->relatedAttribute ? $model->{$this->relatedAttribute}: null,
            'relatedAttribute1' => $this->relatedAttribute1,
            'relatedValue1' => $this->relatedAttribute1 ? $model->{$this->relatedAttribute1}: null,
            'relatedAttribute2' => $this->relatedAttribute2,
            'relatedValue2' => $this->relatedAttribute2 ? $model->{$this->relatedAttribute2}: null,
            'actionParams' => eval('return '.$this->actionParams.';')
        );
    }
    
    /**
     * Validates the content of the actual custom field.
     * @param CModel $object the object being validated
     * @param string $attribute the attribute being validated
     */
    protected function validateField($object, $attribute)
    {
        (new \yii\validators\NumberValidator([
            'integerOnly' => true,
            'min' => 0,
            'max' => 99999999999 // Matches the max size of INT(11) in SQL.
        ]))->validateAttribute($object, $attribute);
    }
}
