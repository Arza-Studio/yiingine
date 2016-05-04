<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

/** 
 * A base class for field behaviors.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 * */
abstract class Base extends \yii\base\Behavior
{    
    /** 
     * @var CustomField the field this behavior is for.
     * NOTE: normally priuvate but PHP throws "serialize(): "_field" returned as member variable from __sleep() but does not exist".
     * */
    public $_field;
    
    /**
     * @var string the name of the attribute this behavior is for.
     * NOTE: normally priuvate but PHP throws "serialize(): "_attribute" returned as member variable from __sleep() but does not exist".
     * */
    public $_attribute;
    
    /**
     * @return string the name of the custom field model class this manager manages.
     * */
    public static function getFieldModelClass()
    {
        return str_replace('managers', 'models', get_called_class()).'Field';
    }
    
    /** 
     * @inheritdoc.
     * */
    public function __construct($config = [])
    {
        $this->_field = $config['field'];
        unset($config['field']);
        $this->_attribute = isset($config['attribute']) ? $config['attribute']: $this->getField()->name;
        unset($config['attribute']);
        
        parent::__construct($config);
    }
    
    /**
     * PHP magic function to perform cleaning prior to serialization.
     * @return array the name of the attributes to serialize.
     * */
    public function __sleep()
    {
        $attributes = get_object_vars($this);
        
        foreach($attributes as $name => $value)
        {
            // Closures cannot be serialized.
            if(is_object($value) && ($value instanceof \Closure))
            {
                unset($attributes[$name]);
            }
        }
        
        return array_keys($attributes);
    }
    
    /**
     * PHP magic function to perform cleaning prior to deserialization.
     * */
    public function __wakeup()
    {
        foreach($this->field->module->managers[$this->field->typeName()] as $name => $value)
        {
            // Closures could be serialized so restore them.
            if(is_object($value) && ($value instanceof \Closure))
            {
                $this->$name = $value;
            }
        }
    }
    
    /** 
     * @return CustomField the field.
     * */
    public function getField()
    {
        return $this->_field;
    }
    
    /**
     * @return string the attribute.
     * */
    public function getAttribute()
    {
        return $this->_attribute;
    }
    
    /**
     * @return array a list of validation rules for the custom field.
     * */
    public function rules()
    {
        $rules = [];
    
        if($this->getField()->in_forms) //If the field is a form field.
        {
            // Fields in forms can always be searched.
            $rules[] = [$this->getAttribute(), 'safe', 'on' => 'search'];
        }
        else
        {
            /*NOTE: This would be the safe things to do but it prevents models from defining
             their own controls.*/
            //Hidden fields cannot be massively assigned.
            //array_push($rules, array($this->getField()->name, 'unsafe'));
        }
    
        if($this->getField()->default) // If a default is set for the field.
        {
            $rules[] = [$this->getAttribute(), 'default', 'value' => $this->getField()->default, 'except' => 'search'];
        }
    
        if($this->getField()->validator) // If a custom validator was provided.
        {
            $rules[] = [$this->getAttribute(), $this->getField()->validator];
        }

        $rules[] = [$this->getAttribute(), $this->getField()->required ? 'required': 'safe'];
        
        return $rules;
    }
    
    /** 
     * Renders the fields associated with a $model.
     * @param $model CustomizableModelInterface the model to render fields from.
     * @return array an ActiveFormStructure.
     * */
    public static function renderInputs($model)
    {
        if(!($model instanceof \yiingine\modules\customFields\models\CustomizableModelInterface))
        {
            throw new \yii\base\Exception(get_class($model).' does not implement CustomizableModelInterface');
        }
    
        // Render this model's custom fields.
        return require($model->getCustomFieldsModule()->basePath.'/views/_forms/_customFields.php');
    }
    
     /** 
      * @return array controller actions associated with this custom field.
      * */
     public function actions()
     {
         return []; // No actions by default.
     }
    
     /**
      * Renders the input for this custom field but also
      * does some other processing before and after.
      * @return array a Form compatible structure.
      * */
     public final function renderInput()
     {    
         $input = $this->renderInputInternal();
    
         if($this->_field->translatable)
         {
             $input['translatable'] = true;
         }
    
         return $input;
     }
    
     /**
      * Render the content of the field.
      * @return mixed the result of the render.
      */
     public function render()
     {
         return $this->owner->{$this->getField()->name};
     }
    
     /** 
      * Renders the input for this custom field.
      * @return array a Form compatible structure.
      * */
     protected abstract function renderInputInternal();
}
