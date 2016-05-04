<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

use \Yii;

/** A model for custom fields of type FKEY.*/
class FKeyField extends CustomField
{
    const LENGTH = 63;

    /**
     * @return array validation rules for this model's attributes.
     */
    public function rules()
    {
        return array_merge([
            ['min_size, size, translatable', '\yiingine\validators\UnsafeValidator'], //These attributes should not be displayed or used.
            /*Since FKEY relationships would be too complicated to implement using translations
             this field is considered non translatable.*/
            ['translatable', 'default', 'value' => 0],
            ['translatable', 'compare', 'compareValue' => false],
            ['configuration', 'required'],
        ], parent::rules());
    }

    /**
     * Validate the configuration attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateConfiguration($attribute, $params)
    {
        if($this->hasErrors($attribute))
        {
            return;
        } // If there are errors, do not validate.

        $configuration = $this->getConfigurationArray();
        
        /*For the FKEY type, configuration must be array(
         * 'modelClass' => 'Model',
         * 'attribute' => 'attribute',
         * 'queryConditions' => 'type="TYPE"'
         * ) .*/
        
        // Attemps to load all modules in case the referred model is part of another module.
        foreach(Yii::app()->modules as $name => $module)
        {
            Yii::app()->getModule($name);
        }
        
        if(!isset($configuration['modelClass']) || !isset($configuration['attribute']) || !isset($configuration['queryConditions']))
        {
            $this->addError($attribute, Yii::t(\yiingine\modules\customFields\models\CustomField::className(), 'Configuration is invalid.'));
            return;
        }
        
        if(!@class_exists($configuration['modelClass']))
        {
            $this->addError($attribute, Yii::t(__CLASS__, '{class} does not exist or has not been included.', array('{class}' => $values[0])));
            return;
        }

        //Instantiate a model to check if the attribute we want exists.
        $model = $configuration['modelClass'];
        $model = $model::model();
        //If there is no attribute or column by that name.
        if(!array_key_exists($configuration['attribute'], $model->metaData->columns) && !@property_exists($model, $configuration['attribute']))
        {
            // Error.
            $this->addError($attribute, Yii::t(__CLASS__, '{attribute} does not exist in {class}.', array('{class}' => $configuration['modelClass'], '{attribute}' => $configuration['attribute'])));
            return;
        }
        
        try //Try to run the query so see if it will not generate any exceptions.
        {
            $model->findAll(new CDbCriteria(array('condition' => stripslashes($configuration['queryConditions']))));
        }
        catch(CException $e)
        {
            //Error.
            $this->addError($attribute, Yii::t(__CLASS__, 'Query condition "{condition}" is not valid.', array('{condition}' => $configuration['queryConditions'])));
        }
    }

    /** Returns an example configuration for this field to be used inside the descriptions
     * and as a default value. Has to be valid php.
     * @return string an example configuration.*/
    public function getExampleConfiguration()
    {
        return 
'array(
    "modelClass" => "Class",
    "attribute" => "name",
    "queryConditions" => "type=\"TYPE\"",
)';
    }
    
    /** @return string the SQL that describes this field.*/
    public function getSql()
    {
        return Yii::$app->db->schema->queryBuilder->getColumnType('integer').' DEFAULT '.
                ($this->default ? $this->default: 0);
    }
}
