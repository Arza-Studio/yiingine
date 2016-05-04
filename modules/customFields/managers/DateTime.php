<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

use \Yii;
use \yiingine\libs\Functions;

/** Manages a CustomField of type datetime.*/
class DateTime extends Base
{                    
    /**
     * @inheritdoc
     * */
    protected function renderInputInternal()
    {
        return [
            'type' => 'engine.extensions.juiTimePicker.EJuiTimePicker',
            'mode' => 'datetime',
            'options' => [
                'dateFormat' => 'yy-mm-dd',
                'timeFormat' => 'hh:mm:ss',
                'showSecond' => true
            ],
            'language' => Yii::$app->locale->id.(Yii::$app->language == 'en' ? '_us': ''),
            'htmlOptions' => [
                'style' => 'width:150px;'
            ],
        ];
    }
    
    /**
     * @inheritdoc
     * */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [$this->getAttribute(), 'date', 'format' => $this->field->required ? Functions::$MySQLDateTimeYiiFormat : [Functions::$MySQLDateTimeYiiFormat, '0000-00-00 00:00:00'], 'skipOnEmpty' => !$this->getField()->required],
        ]);
    }
}
