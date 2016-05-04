<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\models;

use \Yii;

/**
 * A model for custom fields of type ASSOCIATEDMENUITEMS.
 * */
class AssociatedMenuItemsField extends \yiingine\modules\customFields\models\CustomField
{    
    /** 
     * @inheritdoc
     * */
    public function rules()
    {
        $rules = array_merge(parent::rules(), [
            [['min_size', 'size', 'translatable', 'default', 'configuration', 'required'], '\yiingine\validators\UnsafeValidator'], //These attributes should not be displayed or used.
            /*Since ASSOCIATEDMENUITEMS relationships would be too complicated to implement using translations
             this field is considered non translatable.*/
            ['translatable', 'default', 'value' => 0],
            ['in_forms', 'default', 'value' => 0], // This field is always in forms but is handled in a special way.
            ['translatable', 'compare', 'compareValue' => 0],
            ['owners', 'validateUnique']
        ]);
        
        if(!$this->isNewRecord)
        {
            /* Do not allow changing the type of this field once it has been created because it has no
             * column in the database. */
            $rules[] = ['type', '\yiingine\validators\UnsafeValidator'];
        }
        
        return $rules;
    }
    
    /**
     * Validate the types only have one menu item relation.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateUnique($attribute, $params) 
    {
        if($this->hasErrors($attribute)) //Only validate if the attribute has no errors.
        {
            return;
        }
        
        $owners = explode(',', $this->$attribute);
        
        $query = static::find();
        
        if(!$this->isNewRecord)
        {
            $query->where(['not', ['id' => $this->id]]); // Exclude the field currently being modified.
        }
        
        // Iterate through each field of the same type to find owners that already have menu items.
        foreach($query->all() as $field)
        {
            $intersect = array_intersect(explode(',', $field->owners), $owners);
            if(!empty($intersect)) // If a media type already has associated menu items.
            {
                $this->addError($attribute, Yii::t(__CLASS__, '{type} already has a field of this type.', array('type' => $intersect[0])));
            }
        }
    }
    
    /** 
     * @inheritdoc
     * */
    protected function createOrUpdateField() {}
    
    /** 
     * @inheritdoc
     * */
    protected function deleteFieldColumn() {}
    
    /** 
     * @inheritdoc
     * */
    public function getSql() { return false; }
    
    /** @param CDbCriteria $criteria the object to wich a search criteria for this field
     * should be added.
     * @param CModel $model the model being searched.*/
    public function addSearchCriterion($criteria, $model)
    {
        if(!isset($_REQUEST[get_class($model)][$this->name]) || !$_REQUEST[get_class($model)][$this->name])
        {
            return;
        }
        
        $criteria->with[] = $this->name;
        $criteria->together = true;
        //$criteria->join .= ' LEFT OUTER JOINd '.MenuItem::model()->tableName().' `'.$this->name.'` ON '.$this->name.'.parent_id='.$_REQUEST[get_class($model)][$this->name];
        
        $criteria->compare($this->name.'.parent_id', $_REQUEST[get_class($model)][$this->name]);
    }
}
