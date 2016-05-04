<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;

$this->title = Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Account deletion confirmation');

$this->params['breadcrumbs'] = [
    ['url' => ['index'], 'label' => $model->username],
    ['url' => ['edit'], 'label' => Yii::t('generic', 'Edit')],
    Yii::t('generic', 'Delete')
];

?>

<h1 class="page-header"><?php echo $this->title; ?></h1>

<div class="alert alert-warning">
    <?= Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Warning: account deletion is permanent!'); ?>
</div>
<?php \yii\widgets\ActiveForm::begin(); ?>
    <?= Html::hiddenInput('deleteKey', $deleteKey); ?>
    <?= Html::submitButton(Yii::t('generic', 'Yes'), ['onclick' => 'return confirm("'.Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Account deletion is permanent, are you sure you want to proceed?').'");', 'class' => 'btn btn-primary']); ?>
    <?= Html::a(Yii::t('generic', 'Cancel'), ['edit'], ['class' => 'btn btn-primary']); ?>
<?php \yii\widgets\ActiveForm::end(); ?>
