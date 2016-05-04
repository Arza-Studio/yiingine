<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \yii\helpers\Html;
use \Yii;

/**
 * A subclass of Yii's ActiveField to adapt it to use with the Yiingine.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class ActiveField extends \yii\bootstrap\ActiveField
{    
    /** @var If the field is translatable. */
    public $translatable = true;
    
    /** @var array the languages available for translation. */
    protected $languages = [];
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        // Set the languages to its default value.
        if(!$this->languages)
        {
            $this->languages = Yii::$app->getParameter('app.supported_languages');
        }
        
        // The field is not translatable is there are less than two languages.
        $this->translatable = $this->translatable && count($this->languages) > 1;
        
        $this->parts['{input}'] = '';
        
        parent::init();        
    }
    
    /** 
     * @inheritdoc 
     * */
    public function input($type, $options = [])
    {
        //Override of parent implementation to support field translation.
        
        $this->renderInputs('input', $options, $type);
        return $this;
    }
    
    /** 
     * @inheritdoc 
     * */
    public function textInput($options = [])
    {
        //Override of parent implementation to support field translation.
        
        $this->renderInputs('textInput', $options);
        return $this;
    }
    
    /** 
     * @inheritdoc 
     * */
    public function textarea($options = [])
    {
        //Override of parent implementation to support field translation.
        
        $this->renderInputs('textarea', $options);
        return $this;
    }
    
    /** 
     * @inheritdoc 
     * */
    public function radio($options = [], $enclosedByLabel = true)
    {
        //Override of parent implementation to support field translation.
        
        $this->renderInputs('radio', $options);
        return $this;
    }
    
    /** 
     * @inheritdoc 
     * */
    public function checkbox($options = [], $enclosedByLabel = true)
    {
        //Override of parent implementation to support field translation.
        
        $this->renderInputs('checkbox', $options);
        return $this;
    }
    
    /** 
     * @inheritdoc 
     * */
    public function dropDownList($items, $options = [])
    {
        //Override of parent implementation to support field translation.
        
        $this->renderInputs('dropDownList', $options, $items);
        return $this;
    }
    
    /** 
     * @inheritdoc 
     * */
    public function listBox($items, $options = [])
    {
        //Override of parent implementation to support field translation.
        
        $this->renderInputs('listBox', $options, $items);
        return $this;
    }
    
    /** 
     * @inheritdoc 
     * */
    public function checkboxList($items, $options = [])
    {
        //Override of parent implementation to support field translation.
        
        $this->renderInputs('checkboxList', $options, $items);
        return $this;
    }
    
    /** 
     * @inheritdoc 
     * */
    public function radioList($items, $options = [])
    {
        //Override of parent implementation to support field translation.
        
        $this->renderInputs('radioList', $options, $items);
        return $this;
    }
    
    /** 
     * @inheritdoc 
     * */
    public function widget($class, $config = [])
    {
        //Override of parent implementation to support field translation.
        
        $this->renderInputs('widget', $config, $class);
        return $this;
    }
    
    /**
     * Renders an input for each language.
     * Other arguments normally given to the field's input methods should be passed
     * as well.
     * @param string $type the name of the method called.
     * @param array $options the field's options.
     * */
    protected function renderInputs($type, $options)
    {       
        $args = func_get_args();
        array_shift($args); // Type argument not needed.
        $args[] = array_shift($args); // Options gets moved to the end.
        $translatable = isset($options['translatable']) && $options['translatable'];
        unset($options['translatable']);
        $args[count($args) - 1] = &$options; // Restore the reference to the option array in the list of arguments.
        
        // If the field is not translatable.
        if(!$this->translatable || !$translatable)
        {            
            try 
            {
                call_user_func(['parent', $type],  $args[0], isset($args[1]) ? $args[1]: null, isset($args[2]) ? $args[2]: null); // Render the field normally.
                return;
            }
            catch(\yii\base\ErrorException $e)
            {
                //dump($args);
                throw $e;
            }
        }
        
        if($type == 'checkboxList')
        {
            dump($this->attribute);
        }
        
        $input = '';
        $originalAttribute = $this->attribute;
        
        unset($options['id']); // Cannot change the id of translations.
        
        // Render an input for each different language.
        foreach($this->model->getTranslationAttributes($this->attribute) as $language => $attribute)
        {
            $hasErrors = $this->model->hasErrors($attribute);
            $this->attribute = $attribute;
            
            $input .= Html::beginTag('div', ['class' => 'group translation']);
                $input .= Html::beginTag('div', ['class' => 'groupTitle', 'onclick' => 'toggleNodeVisibility(this);']);
                    $input .= Html::activeLabel($this->model, $attribute, ['label' => locale_get_display_language($language, Yii::$app->language)]); //Html::label(locale_get_display_language($language, Yii::$app->language), $this->model->formName().'_'.$this->attribute.'_translations_'.$language);
                    $input .= Html::tag('span', '', ['class' => 'openCloseBtn '.(Yii::$app->language == $language || $hasErrors ? 'fa fa-times-circle' : 'fa fa-plus-circle')]);
                $input .= '</div>';
                
                $input .= Html::beginTag('div', ['class' => 'form-group', 'style' => Yii::$app->language == $language || $hasErrors ? '': 'display:none;']);
                
                call_user_func(['parent', $type], $args[0], isset($args[1]) ? $args[1]: null, isset($args[2]) ? $args[2]: null); // Render the field normally.
                $input .= $this->parts['{input}'];
                           
                $input .= '</div>';
                
                $input .= strpos('{error}', $this->template) === false ? '' : Html::error($this->model, $attribute, $this->errorOptions);
            $input .= '</div>';
        }
        
        $this->attribute = $originalAttribute;
        
        $this->parts['{input}'] = $input;
        $this->parts['{error}'] = ''; // Errors are rendered for each translation.
    }
}
