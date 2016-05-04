<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Url;
use \yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;

// Open the modal dialogue if there are errors.
if($model->hasErrors())
{
    $this->registerJs('$("#'.$this->context->id.'").modal("show")', \yii\web\View::POS_READY);
}

?>
<div class="modal fade" id="<?= $this->context->id; ?>" tabindex="-1" role="dialog" aria-labelledby="searchBoxModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php // Form
            $form = \yiingine\widgets\ActiveForm::begin([
                'enableAjaxValidation' => false,
                'enableClientValidation' => false,
            ]); ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel"><?= FA::icon('search').' '.Yii::t(\yiingine\modules\searchEngine\widgets\SearchBox::className(), 'Search'); ?></h4>
                </div>
                <div class="modal-body">
                    <?= $form->field($model, 'query')->textInput()->label(false); ?>
                </div>
                <div class="modal-footer">
                    <?php 
                    // Close button
                    echo Html::button(Yii::t('generic', 'Close'), ['class'=>'btn btn-default', 'data-dismiss'=>'modal']);
                    // Search button
                    echo Html::submitButton(Yii::t(\yiingine\modules\searchEngine\widgets\SearchBox::className(), 'Search'), ['class'=>'btn btn-primary']);
                    ?>
                </div>
            <?php \yiingine\widgets\ActiveForm::end(); ?>
        </div>
    </div>
</div>
