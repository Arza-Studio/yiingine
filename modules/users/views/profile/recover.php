<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$this->title = Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Password recovery') ;

$this->params['breadcrumbs'][] = Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Login');
$this->params['breadcrumbs'][] = Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Password recovery');
?>

<h1><?= Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Password recovery'); ?></h1>

<div class="form container-fluid">
    <div class="row">
        <?php
            $form = \yiingine\widgets\ActiveForm::begin([
                'id' => 'recovery-form',
                'validateOnBlur' => false,
                'validateOnChange' => false,
                'validateOnSubmit' => false,
                'validateOnType' => false
            ]);
        ?>
            <?= $form->field($model, 'loginOrEmail')->textInput()->hint(Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Please enter your user name or email address.')); ?>
            <div class="form-group">
                <?= \yii\helpers\Html::submitButton(Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Send'), ['class' => 'btn btn-primary']) ?>
            </div>
        <?php \yiingine\widgets\ActiveForm::end();?>
    </div>
</div>
