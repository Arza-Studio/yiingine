<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Url;
use \yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;
use \yiingine\extensions\searchEngine\components\SearchBox;

?>
<button class="hidden-lg btn btn-primary form-group" data-toggle="modal" data-target="#searchBoxModal" role="button" aria-expanded="false" title="<?= Yii::t(get_class($this->context), 'Search'); ?>"><?= FA::icon('search'); ?></button>
<div class="visible-lg navbar-form form-group">
    <?php // Form
    $form = \yiingine\widgets\ActiveForm::begin([
        'enableAjaxValidation' => false,
        'enableClientValidation' => false,
    ]); ?>
    <?= $form->field($model, 'query', ['inputOptions' => ['class' => 'form-control'], 'options' => ['tag' => 'span']])->textInput()->label(false)->error(false); ?>
    <?= Html::submitButton(FA::icon('search'), ['class'=>'btn btn-primary', 'title' => Yii::t(get_class($this->context), 'Search')]); ?>
    <?php \yiingine\widgets\ActiveForm::end(); ?>
</div>
