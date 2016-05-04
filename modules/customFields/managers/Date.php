<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

use \Yii;
use \yiingine\libs\Functions;

/** 
 * Manages a CustomField of type date.
 * */
class Date extends Base
{                
    /**
     * @inheritdoc
     * */
    protected function renderInputInternal()
    {
        return ['type' => 'text'];
        /*return [
            'type' => '\yii\jui\DatePicker',
            'language' => Yii::$app,
            'options' => [
                'showAnim' => 'fold',
                'dateFormat' => 'yy-mm-dd',
            ],
            'htmlOptions' => [
                'style' => 'width:100px;'
            ]  
        ];*/
    }
    
    /**
     * @inheritdoc
     * */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [$this->getAttribute(), 'date', 'format' => $this->field->required ? Functions::$MySQLDateYiiFormat : [Functions::$MySQLDateYiiFormat, '0000-00-00'], 'skipOnEmpty' => !$this->getField()->required],
        ]);
    }
}
