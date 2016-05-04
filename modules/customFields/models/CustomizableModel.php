<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

use \Yii;

/** 
 * A base model class for models implementing CustomizableModelInterface.
*/
abstract class CustomizableModel extends \yiingine\db\TranslatableActiveRecord implements CustomizableModelInterface
{                    
    /**
     * @inheritdoc
     * */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // Add behaviors for all fields.
        foreach($this->getManagers() as $attribute => $manager)
        {
             $behaviors[$attribute.'Behavior'] = $manager;
        }

        // This behavior must be added last.
        $behaviors[SaveAgainBehavior::className()] = SaveAgainBehavior::className();
        
        return $behaviors;
    }
    
    /**
     * @var array caching variable for the translatableAttributes.
     * */
    private $_translatableAttributes;
    
    /** 
     * @inheritdoc 
     * */
    public function translatableAttributes()
    {
        if($this->_translatableAttributes && !CONSOLE)
        {
            return $this->_translatableAttributes;
        }
        
        $this->_translatableAttributes = [];
        
        foreach($this->getFieldModels() as $field)
        {
            if($field->translatable)
            {
                $this->_translatableAttributes[] = $field->name;
            }
        }
        
        return $this->_translatableAttributes;
    }
    
    /** @var array caching variable for rules.*/
    private $_rules;
    
    /**
     * @inheritdoc
     * */
    public function rules()
    {            
        if(!isset($this->_rules)) // If this is the first time the rules are being fetched.
        {
            $rules = [];
            
            foreach($this->getManagers() as $manager)
            {
                $rules = array_merge($rules, $manager->rules());
            }
            
            $this->_rules = $rules;
        }
        
        return $this->_rules;
    }
    
    /**
     * @inheritdoc
     * */
    public function getFields($hidden = true)
    {    
        Yii::trace('yiingine\modules\customFields\models\CustomizableModel.getFields()');
        
        //Cache fields as an object attribute so they do not have to be retrieved again.
        $fields = $this->getFieldModels();
        
        if(!$hidden) //If we do not want hidden fields.
        {
            foreach($fields as $key => $value)
            {
                if(!$value->in_forms) //If the field is hidden.
                {
                    unset($fields[$key]); //Remove it from the list.
                }
            }
        }
        
        return $fields;
    }
    
    /**
     * @inheritdoc
     * */
    public function getField($name)
    {
        return ($behavior = $this->getBehavior($name.'Behavior')) ? $behavior->getField(): null;
    }
    
    private $_managers;
    
    /** 
     * @inheritdoc
     * */
    public function getManagers()
    {
        if($this->_managers)
        {
            return $this->_managers;
        }
        
        $managers = [];
        $factory = $this->getCustomFieldsModule()->factory;
        
        foreach($this->getFields() as $field)
        {
            if($field->translatable)
            {
                foreach($this->getTranslationAttributes($field->name) as $language => $attribute)
                {
                    $managers[$attribute] = $factory->createManager($field, $attribute);
                }
            }
            else
            {
                $managers[$field->name] = $factory->createManager($field);
            }
        }
        
        return $this->_managers = $managers;
    }
    
    /**
     * @inheritdoc
     * */
    public function getManager($name)
    {
        if($this->autoTranslate && $this->hasTranslation($name) && Yii::$app->language !== Yii::$app->getBaseLanguage())
        {
            $name .= '_'.Yii::$app->language;
        }
        
        return $this->getBehavior($name.'Behavior');
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        
        foreach($this->getFields() as $field)
        {
            $labels[$field->name] = $field->title;
        }
    
        return $labels;
    }
    
    /**
     * @inheritdoc
     * */
    public function attributeDescriptions()
    {
        $descriptions = parent::attributeDescriptions();
        foreach($this->getFields() as $field)
        {
            $descriptions[$field->name] = $field->description;
        }
        return $descriptions;
    }
    
    /**
     * @inheritdoc
     * */
    public function link($name, $model, $extraColumns = [])
    {
        $field = $this->getField($name);
        
        // If a link is being made through a ManyToMany field.
        if($field && $field->type == \yiingine\modules\customFields\models\ManyToManyField::className())
        {
            $extraColumns['relation_id'] = $field->id; // Specify the id of the relation.
            
            if(!isset($extraColumns['relation_position'])) // If no position was set.
            {
                $extraColumns['relation_position'] = 1;
            }
        }
        
        return parent::link($name, $model, $extraColumns);
    }
}

/** A behavior that will save a model again in case any previous behavior 
 * modified its content by comparing its attributes between the beforeSave
 * and afterSave events. To work correctly, this behavior must be added last.*/
class SaveAgainBehavior extends \yii\base\Behavior
{
    /** @var array the model's attributes before saving.*/
    private $_attributes;
    
    /**
     * @inheritdoc
     * */
    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
        ];
    }
    
    
    /** Stores the attributes for later comparison.
     *  @param Event $event the event parameters.*/
    public function beforeSave($event)
    {
        $this->_attributes = $this->owner->getAttributes();
    }
    
    /** Save the model again if it has been modified.
     *  @param Event $event the event parameters.*/
    public function afterSave($event)
    {
        //If any attributes has changed, the model needs to save again.
        if($this->_attributes != $this->owner->getAttributes())
        {
            //Do not use the save() method otherwise this would call all the beahviors again.
            $this->owner->updateAll($this->owner->getAttributes(), ['id' => $this->owner->getOldPrimaryKey()]);
        }
    }   
}
