<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

use \Yii;

/** Manages a CustomField of type varchar.*/
class Varchar extends Base
{        
    /** @var integer the maximum width (in percent) of the input.*/
    public $maxInputSize = 80;
    
    /** @var array the default configuration array for CountChar. */
    public $defaultConfiguration = [
        'warningLimit' => 0,
        'errorLimit' => 0,
        'mode' => 'additional',
        'locked' => false,
    ];
    
    /** 
     * @inheritdoc 
     * */
    protected function renderInputInternal()
    {        
        if($this->field->size)
        {
            $size = $this->field->size > $this->maxInputSize ? $this->maxInputSize : $this->field->size ;
            // The maximum width is 98%
            $width = ceil($size * 98 / $this->maxInputSize);
        }
        else
        {
             $width = 98;
        }
        
        return [
            'type' => 'text',
            'style' => 'width:'.$width.'%;',
            $this->getCharacterCounter()
        ];
    }
    
    /** 
     * Add the character counter widget if required.
      * @return string the widget's html.
      */
    protected function getCharacterCounter()
    {
        $config = $this->defaultConfiguration;
        $result = ''; // The rendering result of the widgets.
        
        // By default the error limit is provided by the field size if this one has been filled
        if($this->field->size)
        { 
            $config['errorLimit'] = $this->field->size;
        }
        if($this->field->configuration != '') // If a special configuration was provided.
        {
            // Recursively merge the two configurations with overwrites.
            $config = array_replace_recursive($config, $this->getField()->getConfigurationArray());
        }
        if($config['warningLimit'] || $config['errorLimit'])
        {
            $widgetConfiguration = [
                'warningLimit' => $config['warningLimit'],
                'errorLimit' => $config['errorLimit'],
                'mode' => $config['mode'],
                'locked' => $config['locked'],
            ];
            
            $inputId = \yii\helpers\Html::getInputId($this->owner, $this->field->name);
            
            if(count(Yii::$app->getParameter('app.supported_languages')) > 1 && $this->field->translatable)
            {
                // Add a widget to cound the characters for each language.
                foreach(Yii::$app->getParameter('app.supported_languages') as $language)
                {
                    $widgetConfiguration['inputSelector'] = $inputId.'_translations_'.$language;
                    $result .= \yiingine\widgets\CountChar::widget($widgetConfiguration);
                }
            }
            else
            { 
                // Add a widget to count the characters.
                $widgetConfiguration['inputSelector'] = $inputId;
                $result .= \yiingine\widgets\CountChar::widget($widgetConfiguration);
            }
        }
        
        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function validateField($object, $attribute)
    {
        $validator = new \yii\validators\StringValidator();
        
        // If an exact size is wanted for this field.
        if($this->field->size && $this->field->size == $this->field->min_size)
        {
            $validator->length = $this->field->size;
        }
        else
        {
            // With a field of this type, size is always specified.
            $validator->max = $this->field->size ? $this->field->size : null;
            $validator->min = $this->field->min_size ? $this->field->min_size : null;
        }
        
        $validator->validateAttribute($object, $attribute);
    }
}
