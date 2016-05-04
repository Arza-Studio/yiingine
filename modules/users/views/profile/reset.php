<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$this->title = Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Password reset');

$this->params['breadcrumbs'][] = Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Login');
$this->params['breadcrumbs'][] = $this->title
?>

<h1><?php echo Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Password reset'); ?></h1>

<div class="form container-fluid">
    <div class="row">
        <?php
            $form = \yiingine\widgets\ActiveForm::begin([
                'id' => 'password-reset-form',
                'validateOnBlur' => false,
                'validateOnChange' => false,
                'validateOnSubmit' => false,
                'validateOnType' => false
            ]);
        ?>
            <?= $form->field($model, 'password')->passwordInput(); ?>
            <?= $form->field($model, 'verifyPassword')->passwordInput(); ?>
            <div class="form-group">
                <?= \yii\helpers\Html::submitButton(Yii::t('generic', 'Save'), ['class' => 'btn btn-primary']) ?>
            </div>
        <?php \yiingine\widgets\ActiveForm::end();?>
    </div>
</div>
