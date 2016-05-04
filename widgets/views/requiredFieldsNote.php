<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

echo \yii\helpers\Html::tag($this->context->tag, Yii::t(get_class($this->context), 'Fields with {required} are required.', ['required' => $this->context->marker]), $this->context->options); ?>
