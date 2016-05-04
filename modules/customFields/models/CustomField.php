<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

use \Yii;

/** The CustomField model represents a custom media field. It is associated
 * to a table defined from within the module's configuration.
 * */
class CustomField extends \yiingine\db\TranslatableActiveRecord implements \yiingine\db\AdministrableInterface
{        
    /** @var array a cache for blank models. */
    private static $_models = [];
    
    /** @var a runtime cache where customizableModels should store fields
     * indexed by caching and module id. That way, the runtime cache can be cleared if a field changes.*/
    public static $cache = [];
    
    /** @var CustomFieldsModule the module this custom field belongs to. Used for static usage of the class.*/
    public static $module = null;
    
    /** @var string the module this custom field belongs to. Used for objects of this class.*/
    private $_module = null;
    
    /** @return string the type name of this class as stored in the database.*/
    public static function typeName()
    {
        $class = self::shortClassName();
        // The type of the model is its class name minus "Field".
        return  lcfirst(substr($class, 0, strlen($class) - 5));
    }
    
    /**
     * @inheritdoc
     * @param CustomFieldsModule $module the module the custom fields belong to.
     * @param boolean strict if only the models of this class can be returned.
     * */
    public static function find($module = null, $strict = true)
    {
        if($module !== null)
        {
            self::$module = $module;
        }
        
        $query = parent::find()->orderBy('position');
        
        // If the generic model for CustomFiels is being used or the query is not strict. 
        if(self::shortClassName() == 'CustomField' || !$strict)
        {
            return $query;
        }
        
        // A specific model is being used so only search for its type.
        return $query->where(['type' => self::className()]);
    }
    
    /** 
     * @inheritdoc
     * @param Module $owner the customFields module that owns this field.
     * */
    public function __construct($owner = null, $config = [])
    {        
        if($owner === null)
        {
            $this->_module = self::$module->uniqueId;
        }
        else
        {
            // CustomField models need to know the module they belong to.        
            if(!($owner instanceof \yiingine\modules\customFields\CustomFieldsModule))
            {
               throw new \yii\base\Exception('Called constuctor on CustomField without providing valid module.');
            }
            
            self::$module = $owner;
            $this->_module = $owner->uniqueId;   
        }
        
        parent::__construct($config);
    }
    
    /** @return string a string representation of the model. */
    public function __toString()
    {
        return $this->getModule()->uniqueId.': '.$this->title;
    }
    
    /** 
     * @inheritdoc
     * */
    public function init()
    {
        parent::init();
        
        // Set the model's type according to its name.
        if(self::shortClassName() != 'CustomField')
        {
            $this->type = self::className();
            
            $this->configuration = $this->getExampleConfiguration();
        }
    }
    
    
    /** @return CustomFieldsModule the module this object belongs to. */
    public function getModule()
    {
        if(!$this->_module)
        {
            $this->_module = self::$module->uniqueId;
        }
        
        return Yii::$app->getModule($this->_module);
    }
    
    /** 
     * @param boolean $quiet quiet eval errors.
     * @return mixed the field's configuration or false if it does not exist.
     * */
    public function getConfigurationArray($quiet = false)
    {
        if(!$this->isAttributeSafe('configuration')) // If the field does not use configuration. 
        {
            return false;
        }
        
        $model = $this;
        
        $configuration = $this->configuration;
        
        $return = strpos($configuration, 'return') === 0 ? '': 'return ';
        
        return $quiet ? @eval($return.$configuration.';'): eval($return.$configuration.';');
    }
    
    /**
     * @return string the associated database table name.
     */
    public static function tableName()
    {
        return self::$module->tableName;
    }
    
    /**
    * @see \yiingine\db\ModelInterface::getModelLabel()
    */
    public static function getModelLabel($plural = false)
    {
        return Yii::t(__CLASS__, '{n, plural, =1{Custom field}other{Custom fields}}', ['n' => $plural ? 2 : 1]);
    }
    
    /**
     * @inheritdoc
     * */
    public function translatableAttributes()
    {
        return ['title', 'description', 'default'];
    }
    
    /**
     * @inheritdoc
     * */
    public function rules()
    {
        $modelClass = $this->getModule()->modelClass;
        
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        //For some validators to work, type should be set in priority.
        $rules = [
            [['name', 'title', 'type'], 'required'],
            ['name', 'match', 'pattern' => '/^[A-Za-z_0-9]+$/u'],
            ['name', 'unique'],
            //The name must not be part of the existing columns in the model's table.
            ['name', 'in', 'not' => true, 'range' => array_keys($modelClass::getTableSchema()->columns), 'when' => function($model){ return $model->isNewRecord; }],
            [['name', 'type'], 'string', 'max' => 255],
            [['required', 'in_forms', 'translatable'], 'boolean'],
            ['type', 'in', 'range' => array_values($this->getModule()->factory->getTypes())],
            [['size', 'min_size', 'position', 'form_group_id'], 'integer', 'integerOnly' => true, 'min' => 0],
            [['description', 'default'], 'safe'],
            [['position', 'in_forms'], 'default', 'value' => 1],
            ['form_group_id', 'exist', 'targetAttribute' => 'id', 'targetClass' => '\yiingine\modules\customFields\models\FormGroup', 'when' => function($model) { return $model->form_group_id != 0; }],
            [['title', 'validator'], 'string', 'max' => 255],
            ['size', 'validateSize'],
            ['min_size', 'validateMinSize'],
            ['position', 'validatePosition'],
            ['in_forms', 'validateInForms'],
            ['translatable', 'validateTranslatable'],
            ['configuration', 'validateConfigurationEvaluates'],
            ['default', 'validateDefaultInternal'],
            ['validator', 'validateValidatorInternal'],
            [$this->attributes(), 'safe', 'on' => 'search'] // All attributes can be searched.
        ];
        
        $validator = new \yii\validators\RangeValidator([
            'range' => array_values($this->getModule()->factory->getTypes())
        ]);
        
        //Add required or safe rules for each field parameter.
        foreach($this->getModule()->getFieldParameters() as $param)
        {
            $rules[] = [$param->name, $param->required ? 'required': 'safe'];
        }
        /*The validation of those parameters will be done from within the after validate event because we
         * need to get them from the module using getFieldParams().*/ 
        
        return $rules;
    }
    
    /**
     * Validate the size attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateSize($attribute, $params) {}
    
    /**
     * Validate the min_size attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateMinSize($attribute, $params) {}
    
    /**
     * Validate the position attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validatePosition($attribute, $params) {}
    
    /**
     * Validate the in_forms attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateInForms($attribute, $params) {}
    
    /**
     * Validate the translatable attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateTranslatable($attribute, $params) {}
    
    /**
     * Validate the configuration attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateConfiguration($attribute, $params) {}
    
    /**
     * Validate the configuration attribute evaluates.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateConfigurationEvaluates($attribute, $params)
    {
        try
        {
            $result = $this->getConfigurationArray(true);
            
            if($result === false)
            {
                $this->addError($attribute, Yii::t(__CLASS__, 'Syntax error!'));
            }
            
            if(!is_array($result))
            {
                $this->addError($attribute, Yii::t(__CLASS__, 'Configuration must return an array.'));
            }
        }
        catch (Exception $e) // If an exception is thrown.
        {
            $this->addError($attribute, Yii::t(__CLASS__, 'Configuration throws exception: {e}.', array('{e}' => $e->getMessage())));
        }
        
        if(!$this->hasErrors($attribute)) // If no errors with the configuration were detected.
        {
            $this->validateConfiguration($attribute, []); // Validate the content of the configuration.
        }
    }
    
    /**
     * Validate the validator attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public final function validateValidatorInternal($attribute, $params) 
    {
        /*If configuration does not contain spaces and contains the word Validator, it probably
         * referes to a validator class.*/
        if($this->$attribute)
        {
            if(!@class_exists($this->$attribute)) //If the class refered to by validator does not exist.
            {
                $this->addError($attribute, Yii::t(__CLASS__, 'The validator class {class} cannot be found.', array('{class}' => $this->$attribute)));
                return;
            }
        
            $hierarchy = [];
            $class = $this->$attribute;
            //Loop to get all parent classes of the validator.
            while($class = get_parent_class($class))
            {
                array_push($hierarchy, $class);
            }
        
            if(!in_array('CValidator', $hierarchy)) //Check if that class inherits from CValidator.
            {
                $this->addError($attribute, Yii::t(__CLASS__, '{class} must inherit from CValidator.', array('{class}' => $this->$attribute)));
            }
        }
        else //Let the child class validate the validator attribute.
        {
            $this->validateValidator($attribute, $params);
        }
    }
    
    /**
     * Validate the validator attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateValidator($attribute, $params) {}
    
    /**
     * Validate the default by checking if it
     * validates with the actual custom field manager.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public final function validateDefaultInternal($attribute, $params) 
    {
        //If the default value is empty or required attributes have errors.
        if(!$this->$attribute || $this->hasErrors('type') || $this->hasErrors('configuration'))
        {
            return;
        }
        
        // Validate default using the manager for this field.
        $model = \yii\base\DynamicModel::validateData([$attribute], $this->getModule()->factory->createManager($this, $attribute)->rules());
        
        if($model->hasErrors())
        {
            $this->addErrors($model->getErrors());
        }
        
        // Use the normal validator as well.
        $this->validateDefault($attribute, $params);
    }
    
    /**TableName
     * Validate the default attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateDefault($attribute, $params) {}
    
    /**
     * @return array the form group this field belongs to.
     * */
    public function getFormGroup()
    {
        return $this->hasOne(FormGroup::className(), ['id' => 'form_group_id']);
    }
    
    /** Override of parent implementation to validate the field parameters.
     * @return boolean if validation should proceed.*/
    public function beforeValidate()
    {
        foreach($this->getModule()->getFieldParameters() as $param)
        {
            $param->validate($this);
        }
        
        return parent::beforeValidate();
    }
    
    /**
     * @inheritdoc
     */  
    public function afterSave($insert, $oldAttributes)
    {
        $this->createOrUpdateField($insert, $oldAttributes);
              
        Yii::$app->db->schema->refresh(); // Reload the schema because it has changed.
        
        self::$cache = [];
        
        return parent::afterSave($insert, $oldAttributes);
    }
    
    /** 
     * Create or update the field on the customizable table.
     * @param boolean $insert if the record was inserted.
     * @param arrat $oldAttributes the value of the attributes stored in database.
     * */
    protected function createOrUpdateField($insert, $oldAttributes)
    {
        $modifySchema = false; //If the column schema must be modified.
        $modelClass = $this->getModule()->modelClass;
        $languages = array_diff(Yii::$app->getParameter('app.supported_languages'), [Yii::$app->getBaseLanguage()]);
        
        // Just to make sure all attributes are there.
        $oldAttributes = array_merge($this->attributes, $oldAttributes);
        
        if(!$insert) //If the record was not new.
        {
            //Then the column schema may not need to be modified.
            
            if($this->type !== $oldAttributes['type']) // If the type changed.
            {
                $modifySchema = true;
            }
            else if((int)$this->size !== $oldAttributes['size']) // If the size changed.
            {
                $modifySchema = true;
            }
            else if($this->default !== $oldAttributes['default']) // If the default value changed.
            {
                $modifySchema = true;
            }
            else if($this->name !== $oldAttributes['name']) // If the name changed.
            {
                $modifySchema = true;
            }
        }
        else
        {
            $modifySchema = true; // It is a new record, schema needs modification.
        }
        
        $connection = $modelClass::getDb();
        $transaction = $connection->beginTransaction();
        
        if($modifySchema)
        {
            // Build the SQL command.
            if(!$connection->schema->getTableSchema($modelClass::tableName())->getColumn($this->name)) // If the column does not exist, it must be added.
            {
                $connection->createCommand()->addColumn($modelClass::tableName(), $this->name, $this->getSql())->execute();
            }
            else // The column already exists.
            {
                if($oldAttributes['name'] !== $this->name) // If the name of the column should be changed.
                {
                    // Do it first.
                    $connection->createCommand()->renameColumn($modelClass::tableName(), $oldAttributes['name'], $this->name)->execute();
                    
                    if($oldAttributes['translatable']) // If the column has translations.
                    {
                        // Change the name of the translation column as well.
                        foreach($languages as $language)
                        {
                            $connection->createCommand()->renameColumn($modelClass::tableName(), $oldAttributes['name'].'_'.$language, $this->name.'_'.$language)->execute();
                        }
                    }
                }
                
                // Then alter the column to its new type.
                $connection->createCommand()->alterColumn($modelClass::tableName(), $this->name, $this->getSql())->execute();
                
                if($oldAttributes['translatable']) // If the column has translations.
                {
                    // Alter the translation columns as well.
                    foreach($languages as $language)
                    {
                        $connection->createCommand()->alterColumn($modelClass::tableName(), $this->name.'_'.$language, $this->getSql())->execute();
                    }
                }
                // Default is not updated on the target table as this could create conflicts.
            }
        }
        
        // If translatable changed.
        if($insert || ($oldAttributes['translatable'] !== (int)$this->translatable))
        {
            foreach($languages as $language)
            {
                if($this->translatable) // If the field has become translatable.
                {
                    $connection->createCommand()->addColumn($modelClass::tableName(), $this->name.'_'.$language, $this->getSql())->execute();
                }
                else if(!$insert) // The field is no longer translatable and is not a new record.
                {
                    $connection->createCommand()->dropColumn($modelClass::tableName(), $this->name.'_'.$language)->execute();
                }
            }
        }

        
        $transaction->commit();
        
        $transaction = $connection->beginTransaction();
        
        if($insert && $this->default) //If this is a new record and it has a default value.
        {
            //Set all rows of this column to the default value.
            $connection->createCommand()->update($modelClass::tableName(), [$this->name => $this->getAttribute('default', Yii::$app->getBaseLanguage())])->execute();
            
            if($this->translatable)
            {
                // Set default values for translation columns as well.
                foreach($languages as $language)
                {
                    $connection->createCommand()->update($modelClass::tableName(), [$this->name.'_'.$language => $this->getAttribute('default', $language)])->execute();
                }
            }
        }
        
        $transaction->commit();
        
        $connection->schema->refresh(); // Reload the schema because it has changed.
    }
    
    /**
     * Override of parent implementation to prevent deletion of protected fields.
     * @return boolean if the saving can proceed.
     */
    public function beforeDelete()
    {
        // Protected fields can be deleted in CONSOLE mode.
        if($this->protected && !CONSOLE) // If the field is protected.
        {
            return false; // It cannot be deleted.
        }
        
        return parent::beforeDelete();
    }
    
    /**
     * Override of parent implementation to commit schema changes on the customizable table.
     */
    public function afterDelete()
    {        
        $this->deleteFieldColumn();
        
        self::$cache = [];
        
        return parent::afterDelete(); //Calls the parent.
    }
    
    /** 
     * Delete the field from the customizable table.
     * */
    protected function deleteFieldColumn()
    {
        $modelClass = $this->getModule()->modelClass;
        $languages = array_diff(Yii::$app->getParameter('app.supported_languages'), [Yii::$app->getBaseLanguage()]);
        
        $columnsToDrop = [$this->name];
        
        if($this->translatable)
        {
            foreach($languages as $language)
            {
                $columnsToDrop[] = $this->name.'_'.$language;
            }
        }
        
        $command = $modelClass::getDb()->createCommand();
        
        foreach($columnsToDrop as $column)
        {
            // If for whatever reason the column does not exist.
            if($modelClass::getDb()->schema->getTableSchema($modelClass::tableName())->getColumn($column) === null)
            {
                continue; // Skip it, it may have been deleted manually.
            }
            
            $command->dropColumn($modelClass::tableName(), $column);
        }

        $command->execute();
        
        $modelClass::getDb()->schema->refresh(); // Reload the schema because it has changed.
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array_merge(parent::attributeLabels(), [
            'description' => Yii::t(__CLASS__, 'Description'),
            'name' => Yii::t(__CLASS__, 'Name'),
            'title' => Yii::t(__CLASS__, 'Title'),
            'type' => Yii::t(__CLASS__, 'Type'),
            'form_group_id' => Yii::t(__CLASS__, 'Form group'),
            'size' => Yii::t(__CLASS__, 'Size'),
            'min_size' => Yii::t(__CLASS__, 'Minimum size'),
            'configuration' => Yii::t(__CLASS__, 'Configuration'),
            'validator' => Yii::t(__CLASS__, 'Validator'),
            'default' => Yii::t(__CLASS__, 'Default'),
            'required' => Yii::t(__CLASS__, 'Required'),
            'in_forms' => Yii::t(__CLASS__, 'Display in forms'),
            'translatable' => Yii::t(__CLASS__, 'Translatable'),
            'protected' => Yii::t(__CLASS__, 'Protected'),
        ]);
        
        //Add labels for each parameter.
        foreach($this->getModule()->getFieldParameters() as $param)
        {
            $labels[$param->name] = $param->getTitle();
        }
        
        return $labels;
    }
    
    /** @return array attribute descriptions (name => description). */
    public function attributeDescriptions()
    {
        return array_merge(parent:: attributeDescriptions(), [
            'name' => Yii::t(__CLASS__, 'The database name of this item.'),
            'title' =>  Yii::t(__CLASS__, 'The display title of this item.'),
            'description' => Yii::t(__CLASS__, 'A description that will be displayed with this item.'),
            'type' => Yii::t(__CLASS__, 'The data type of this field.').($this->isAttributeSafe('type') ? '': ' '.Yii::t(__CLASS__, 'Since this field is stored in a special way, its type cannot be changed.')),
            'form_group_id' => Yii::t(__CLASS__, 'The form group this field will be part of.'),
            'required' => Yii::t(__CLASS__, 'If this field can be left blank.'),
            'default' => Yii::t(__CLASS__, 'The field\'s default value.'),    
            'validator' => Yii::t(__CLASS__, 'The name of class inheriting from CValidator can be given to conduct custom validation on the field.'),
            'in_forms' => Yii::t(__CLASS__, 'If the field should have an input in forms.'),
            'translatable' => Yii::t(__CLASS__, 'If the field should have associated translations.'),
            'position' => Yii::t(__CLASS__, 'The position of this field in the form.'),
            'protected' => Yii::t(__CLASS__, 'If deletion or renaming should be forbidden once the field has been created.'),
            'configuration' =>  Yii::t(__CLASS__, 'A configuration array').' :<br/>'.nl2br(str_replace(' ', '&nbsp;', $this->getExampleConfiguration()))
        ]);
    }
    
    /** Returns an example configuration for this field to be used inside the descriptions
     * and as a default value. Has to be valid php.
     * @return string an example configuration.*/
    public function getExampleConfiguration()
    {
        return ''; // No default configuration.
    }
    
    /** @return string|boolean the SQL that describes this field or false if there is none.*/
    public function getSql()
    {
        throw new \yii\base\Exception('Cannot use this method on '.get_class($this));
    }
    
    /**
     * @inheritdoc
     */
    protected function searchInternal($dataProvider)
    {
        $dataProvider = parent::searchInternal($dataProvider);
        
        $dataProvider->query->with('formGroup');
        
        // Add a condition for each parameter.
        foreach($this->getModule()->getFieldParameters() as $param)
        {
            $dataProvider->query->andFilterWhere([$param->name => $this->{$param->name} ]);
        }
        
        return $dataProvider;
    }
    
    /**
     * Override of parent implementation to implement single table inheritance depending on
     * the type of the field being instantiated.
    * @inheritdoc
    */
    public static function instantiate($row)
    {        
        // If the type attribute is not set or is blank.
        if(!isset($row['type']) || !$row['type'])
        {
            throw new \yii\base\Exception('Cannot instantiate a class of type '.__CLASS__);
        }
        
        // If CustomField is used, we instantiate the model using its own class.
        if(self::shortClassName() == 'CustomField')
        {
            $class = $row['type'];
        }
        else // Else a specific class is used so all models get created with this class.
        {
            $class = self::className();
        }
        
        // Return the correct model for this type.
        return new $class(self::$module); // Model needs to be blank.
    }

    /** 
     * @inheritdoc
     * */
    public function getAdminUrl()
    {
        return ['/'.$this->module->uniqueId.'/admin/custom-field/update', 'id' => $this->id];
    }
    
    /** 
     * @inheritdoc
     * */
    public function isAccessible()
    {
        return Yii::$app->user->can(self::formName().'-'.$this->getModule()->module->id.'-view');
    }
    
    /** 
     * @inheritdoc
     * */
    public function getEnabled()
    {
        return true; // CustomFields cannot be disabled.
    }
}
