<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

/** @var array $options the html options of the hidden text link. */
/** @var string $hiddenText the replacement text.  */

use \yii\helpers\Html;

$this->registerJs('initHiddenText("'.\yii\helpers\Url::to(['/api/hiddenText.show']).'");', \yii\web\View::POS_READY);

$text = Html::tag('span', $this->context->message, ['class' => 'hiddenTextMessage']);
$text .= $this->context->loader !== null ? $this->context->loader : Html::tag('i', '', ['class' => 'hiddenTextLoader fa fa-refresh fa-spin hidden']);
$text .= Html::tag('span', $hiddenText, ['class' => 'hiddenTextKey hidden']);

echo Html::a($text, '#', [
    'class' => 'hiddenText'.(isset($option['class']) ? $option['class'] : ''),
    'rel' => isset($option['rel']) ? $option['rel'] : 'nofollow',
    'title' => isset($option['title']) ? $option['title'] : Yii::t(get_class($this->context), 'Click here to see the hidden text.')
]);
