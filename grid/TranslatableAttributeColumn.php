<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\grid;

use \Yii;
use yii\helpers\Html;

/**
 * Displays the translations of a given attribute.
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class TranslatableAttributeColumn extends \yii\grid\DataColumn
{
    /** @var array html options for the other languages, ie languages other
     * than the active one.*/
    public $otherLanguagesHtmlOptions = [
        'style' => 'font-style:italic;font-weight:normal;color:gray;'
    ];
    
    /** @ var string the separator between translations. */
    public $separator = ' | ';
    
    /**
     * @var Closure anonymous function to indicate if the the value to display is translatable on this
     * model instance.
     * */
    public $isTranslatable;
    
    /** @inheritdoc */
    public function init()
    {
        parent::init();
    
        if(!isset($this->options['style'])) // Use a default style if none is set.
        {
            $this->options['style'] = 'display:block;font-weight:bold;font-size:13px;';
        }
    }
    
    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        // If the attribute is not translatable on the model or this instance.
        if(!$model->hasTranslation($this->attribute) ||
            ($this->isTranslatable && !call_user_func($this->isTranslatable, $model, $this->attribute, $this))
        )
        {
            return parent::renderDataCellContent($model, $key, $index);
        }    
        
        $currentLanguage = Yii::$app->language;
        
        $string = Html::beginTag('span', ['style' => 'display:block;font-weight:bold;font-size:13px;']);
        $string .= parent::renderDataCellContent($model, $key, $index);
        
        $string .= Html::beginTag('span', $this->otherLanguagesHtmlOptions);
        
        // Render each translation of the attribute.
        foreach($model->getTranslations($this->attribute) as $language => $translation)
        {
            if($language == $currentLanguage)
            {
                continue;
            }
            
            Yii::$app->language = $language;
            
            if($model->{$this->attribute})
            {
                $string .= $this->separator.parent::renderDataCellContent($model, $key, $index);
            }
        }
        
        Yii::$app->language = $currentLanguage; // Restore the current language.
        
        return $string.'</span></span>';
    }
}
