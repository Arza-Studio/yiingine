<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

use \Yii;

/** A model for custom fields of type ONETOMANY.*/
class OneToManyField extends CustomField
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array_merge([
            ['min_size, size, translatable, default, in_forms', '\yiingine\validators\UnsafeValidator'], //These attributes should not be displayed or used.
            /*Since OneToMany relationships would be too complicated to implement using translations
             this field is considered non translatable.*/
            ['translatable', 'default', 'value' => 0],
            ['in_forms', 'default', 'value' => 1],
            ['translatable', 'compare', 'compareValue' => false],
            ['configuration', 'required'],
        ], parent::rules());
        
        if(!$this->isNewRecord)
        {
            /* Do not allow changing the type of this field once it has been created because it does not 
             * have a column in the database. */
            $rules[] = ['type', '\yiingine\validators\UnsafeValidator'];
        }
        
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function validateConfiguration($attribute, $params)
    {
        if($this->hasErrors($attribute))
        {
            return;
        } // If there are errors, do not validate.

        $configuration = $this->getConfigurationArray();
        
        /* For the ONETOMANY type, configuration must be array(
         * 'modelClass' => 'Model',
         * 'attribute' => 'attribute',
         * 'queryConditions' => 'type="TYPE"',
         * 'associatableModelClasses' => array(array('adminUrl' => adminUrl, 'model' => Model::model()))
         * ) .*/
        
        if(!isset($configuration['modelClass']) || !isset($configuration['attribute']) || !isset($configuration['associatableModelClasses']) || !isset($configuration['queryConditions']))
        {
            $this->addError($attribute, Yii::t(\yiingine\modules\customFields\models\CustomField::className(), 'Configuration is invalid.'));
            return;
        }
        
        //Load all modules in case the wanted class is part of them.
        foreach(Yii::app()->modules as $name => $config)
        {
            Yii::app()->getModule($name);
        }
        
        if(!@class_exists($configuration['modelClass']))
        {
            $this->addError($attribute, Yii::t(__CLASS__, '{class} does not exist or has not been included.', ['{class}' => $configuration['modelClass']]));
            return;
        }
        
        //Instantiate a model to check if the attribute we want exists.
        $model = $configuration['modelClass'];
        $model = $model::model();
        
        //The model must implement the IViewable interface.
        if(!in_array('IViewable', class_implements($model)))
        {
            //Error
            $this->addError($attribute, Yii::t(__CLASS__, 'Model must implement ViewableInterface.'));
            return;
        }

        try //Try to run the query so see if it will not generate any exceptions.
        {
            $model->findAll(new CDbCriteria(array('condition' => $configuration['queryConditions'])));
        }
        catch(CException $e)
        {
            //Error.
            $this->addError($attribute, Yii::t(__CLASS__, 'Query condition "{condition}" is not valid.', ['{condition}' => $configuration['queryConditions']]));
        }
        
        // If there is no attribute or column by that name.
        if(!array_key_exists($configuration['attribute'], $model->metaData->columns) && !@property_exists($model, $configuration['attribute']))
        {
            //Error
            $this->addError($attribute, Yii::t(__CLASS__, '{attribute} does not exist in {class}.', ['{class}' => $configuration['modelClass'], '{attribute}' => $configuration['attribute']]));
            return;
        }
        
        /* Check if the associatableModelClasses array is valid. This array is not evaled along with
         * the rest of the array to prevent recursion for models that can be associated to themselves. */
        if($configuration['associatableModelClasses'])
        {
            $manager = $this->getModule()->factory->createManager($this);
            
            try
            {
                $result = @eval('return '.$configuration['associatableModelClasses'].';');
            }
            catch(CException $e)
            {
                $this->addError($attribute, Yii::t(__CLASS__, 'associatableModelClasses throws exception: {e}.', ['{e}' => $e->getMessage()]));
            }
            
            if($result === false) // If there is a syntax error.
            {
                $this->addError($attribute, Yii::t(__CLASS__, 'associatableModelClasses has syntax error.'));
            }
            
            if(!is_array($result))
            {
                $this->addError($attribute, Yii::t(__CLASS__, 'associatableModelClasses must be an array.'));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getExampleConfiguration()
    {
        return 
'array(
    "modelClass" => "Class",
    "attribute" => "name",
    "queryConditions" => "type=\"TYPE\"",
    "associatableModelClasses" => "array(
        array(
            \"model\" => new Model1(), 
            \"adminUrl\" => \"/admin/url1\",
            \"create\" => false, // An instance of this model cannot be created.
        ),
        array(\"model\" => new Model(type=\"TYPE\"), \"adminUrl\" => \"/admin/url2\"),
    )"
)';
    }
    
    /**
     * @inheritdoc
     */
    protected function createOrUpdateField($insert, $oldAttributes) 
    {
        // This field depends entirely on a FKey field.
    }
    
    /**
     * @inheritdoc
     */
    protected function deleteFieldColumn() {}
    
    /** 
     * @inheritdoc
     * */
    public function getSql()
    {
        return false;
    }
    
}
