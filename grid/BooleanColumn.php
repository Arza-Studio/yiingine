<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\grid;

use \Yii;

/**
 * A column for displaying boolean values.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class BooleanColumn extends \yii\grid\DataColumn
{       
    /**
     * @inheritdoc
     * */
    public function init()
    {
        parent::init();
       
        // If headerOptions are not set.
        if(empty($this->headerOptions))
        {
            $this->headerOptions = [
                'class' => 'enable',
                'width' => 40,
                'style' => 'text-align:center;',
            ];
        }
        
        // If options are not set.
        if(empty($this->contentOptions))
        {
            $this->contentOptions = [
                'style' => 'text-align:center;',
            ];
        }
        
        // If value is not set.
        if(!isset($this->value)) 
        {
            $this->value = function ($model, $key, $index, $column)
            {
                return \rmrevin\yii\fontawesome\FA::icon($model->{$column->attribute} ? 'check' : 'times');
            };
        }
        
        $this->format = 'raw'; // Do not html encode the value of this columns.
        
        $this->filter = [0 => Yii::t('generic', 'No'), 1 => Yii::t('generic', 'Yes')];
    }
}
