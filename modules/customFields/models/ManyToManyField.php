<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

use \Yii;

/** A model for custom fields of type MANYTOMANY.*/
class ManyToManyField extends CustomField
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array_merge([
            [['min_size', 'size', 'translatable', 'default'], '\yiingine\validators\UnsafeValidator'], //These attributes should not be displayed or used.
            /*Since ManyToMany relationships would be too complicated to implement using translations
             this field is considered non translatable.*/
            ['translatable', 'default', 'value' => 0],
            ['in_forms', 'default', 'value' => 1], // This field is almost always in forms.
            ['translatable', 'compare', 'compareValue' => 0],
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
        
        /* For the MANYTOMANY type, configuration must be array(
         * 'modelClass' => 'Model',
         * 'queryConditions' => 'type="TYPE"',
         * 'associatableModelClasses' => array(array('adminUrl' => adminUrl, 'model' => Model::model()))
         * ) .*/
        
        if(!isset($configuration['modelClass']) || !isset($configuration['associatableModelClasses']) || !isset($configuration['queryConditions']))
        {
            $this->addError($attribute, Yii::t(\yiingine\modules\customFields\models\CustomField::className(), 'Configuration is invalid.'));
            return;
        }
        
        if(!@class_exists($configuration['modelClass']))
        {
            $this->addError($attribute, Yii::t(__CLASS__, '{class} does not exist or has not been included.', array('{class}' => $configuration['modelClass'])));
            return;
        }
        
        // Instantiate a model to check if the attribute we want exists.
        $class = $configuration['modelClass'];
        
        // The model must implement the ViewableInterface.
        if(!in_array('yiingine\db\ViewableInterface', class_implements($class)))
        {
            //Error
            $this->addError($attribute, Yii::t(__CLASS__, 'Model must implement ViewableInterface.'));
            return;
        }

        try // Try to run the query so see if it will not generate any exceptions.
        {
            $class::find()->where($configuration['queryConditions'])->all();
        }
        catch(Exception $e)
        {
            //Error.
            $this->addError($attribute, Yii::t(__CLASS__, 'Query condition "{condition}" is not valid.', array('{condition}' => $configuration['queryConditions'])));
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
                $this->addError($attribute, Yii::t(\yiingine\modules\customFields\models\OneToManyField::className(), 'associatableModelClasses throws exception: {e}.', array('{e}' => $e->getMessage())));
            }
            
            if($result === false) // If there is a syntax error.
            {
                $this->addError($attribute, Yii::t(\yiingine\modules\customFields\models\OneToManyField::className(), 'associatableModelClasses has syntax error.'));
            }
            
            if(!is_array($result))
            {
                $this->addError($attribute, Yii::t(\yiingine\modules\customFields\models\OneToManyField::className(), 'associatableModelClasses must be an array.'));
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
    protected function createOrUpdateField($insert, $oldAttributes) {}
    
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
    
    /** 
     * @inheritdoc 
     * */
    public function afterSave($insert, $oldAttributes)
    {
        // Override of parent implementation to create the table for the relationship.
        parent::afterSave($insert, $oldAttributes);
        
        //Get the name of the table where the relation is stored.
        $table = CustomField::getModule()->factory->createManager($this)->getTable();
        
        if(Yii::$app->db->schema->getTableSchema($table)) // If the table already exists.
        {
            return; //Nothing to do.
        }
        
        // The table needs to be created.
       Yii::$app->db->createCommand()->createTable($table, [
            'parent_id' => 'integer NOT NULL',
            'child_id' => 'integer NOT NULL',
            'relation_position' => 'integer NOT NULL',
            'relation_id' => 'integer NOT NULL',
            'PRIMARY KEY (`parent_id`, `child_id`, `relation_id`)'
        ])->execute();
                
        Yii::$app->db->schema->refresh(); //Schema was changed so refresh it.
    }
    
    /**
     * @inheritdoc
     * */
    public function afterDelete()
    {
        // Override of parent implementation to delete relations as well.
        parent::afterDelete();
        
        //Configure the relation helper model.
        ModelModel::$table = CustomField::getModule()->factory->createManager($this)->getTable();
        
        // Delete all relations that we created through this field.
        ModelModel::deleteAll(['relation_id' => $this->id], [], false);
        
        /* Here we would need to clear the cache for the model that owned this field.
         * Since there is no easy way of getting that model's name, we just clear the whole cache.*/
        Yii::$app->cache->flush();
    }
}
