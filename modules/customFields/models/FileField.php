<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

use \Yii;

/** A model for custom fields of type FILE.*/
class FileField extends CustomField
{
    /** @return array behaviors to attach to this model.*/
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'CustomFieldFilePathUpdateBehavior' => ['class' => '\yiingine\modules\customFields\behaviors\CustomFieldFilePathBehavior']
        ]);
    }
    
    /**
     * @return array validation rules for this model's attributes.
     */
    public function rules()
    {
        $rules = array_merge([
            ['default', '\yiingine\validators\UnsafeValidator'], // These attributes should not be displayed or used.
            [['min_size', 'size'], 'default', 'value' => 0], 
            [['configuration', 'size', 'min_size'], 'required'],
            [['min_size', 'size'], 'integer', 'min' => 0, 'integerOnly' => true],  
        ], parent::rules());
        
        if(!$this->isNewRecord)
        {
            /* Do not allow changing the type of this field once it has been created because it handles
             * files on the filesystem. */
            $rules[] = ['type', '\yiingine\validators\UnsafeValidator'];
        }
        
        return $rules;
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
        
        /*For the FILE type, configuration must be array('maximumNumberOfFiles' => 1) .*/
        
        if(!isset($configuration['maximumNumberOfFiles']))
        {
            $this->addError($attribute, Yii::t(\yiingine\modules\customFields\models\CustomField::className(), 'Configuration is invalid.'));
        }
    }
    
    /** @return array attribute descriptions (name => description). */
    public function attributeDescriptions()
    {
        return array_merge(parent::attributeDescriptions(), [
            'size' =>  Yii::t(__CLASS__, 'An integer specifying the maximum size in bytes of each file. Set to 0 for no limit.'),
            'min_size' =>  Yii::t(__CLASS__, 'An integer specifying the minimum size in bytes of each file. Set to 0 for no limit.'),
        ]);
    }

    /** Returns an example configuration for this field to be used inside the descriptions
     * and as a default value. Has to be valid php.
     * @return string an example configuration.*/
    public function getExampleConfiguration()
    {
        return 
'array(
    "maximumNumberOfFiles" => 1 // '.Yii::t(__CLASS__, 'An integer specifying the limit of files that can be uploaded.').',
)';
    }
    
    /** @return string the SQL that describes this field.*/
    public function getSql()
    {
        return Yii::$app->db->schema->queryBuilder->getColumnType('text'); 
    }
    
    /**
     * @inheritdoc
     * */
    public function beforeDelete()
    {        
        $class = CustomField::getModule()->modelClass;
        
        foreach($class::find()->where(['not', [$this->name => null, $this->name => '']])->all() as $model)
        {
            // Simulate a deletion of the model to get rid of the files.
            $model->getManager($this->name)->beforeDelete(new \yii\base\ModelEvent());
        }
            
        return parent::beforeDelete();
    }
}
