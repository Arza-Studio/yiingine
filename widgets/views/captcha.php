<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
?>

<div class="form-group">
    <?= \yii\captcha\Captcha::widget(array_merge($options, ['captchaAction' => $captchaAction, 'imageOptions' => ['class' => 'no-optimization', 'style' => 'cursor:pointer; margin-bottom:5px', 'alt' => Yii::t(get_class($this->context), 'Click on the image to refresh the code.')], 'model' => $model, 'attribute' => $attribute])); ?>
    <span class="help-block">
        <?php echo Yii::t(get_class($this->context), 'Please enter the letters as they are shown in the image above.'); ?><br/>
        <?php echo Yii::t(get_class($this->context), 'Click on the image to refresh the code.').'<br/>'; ?>
        <?php echo Yii::t(get_class($this->context), 'Letters are not case-sensitive.'); ?>
    </span>
</div>
