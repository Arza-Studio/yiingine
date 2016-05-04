<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use \yii\helpers\Url;

$this->title = Yii::t(\yiingine\modules\users\controllers\DefaultController::className(), 'Login');

$this->params['breadcrumbs'] = [$this->title];

// A common message is shown if either the username or the password is incorrect to avoid giving clues about the username being correct.
if($model->getErrors('username') || $model->getErrors('password'))
{
    $model->clearErrors();
    $model->addError('username', Yii::t(\yiingine\modules\users\controllers\DefaultController::className(), 'Username or password incorrect'));
}

?>
<div class="container">
    <div class="page-header">
        <h1><?= rmrevin\yii\fontawesome\FA::icon('sign-in').' '.Yii::t(\yiingine\modules\users\controllers\DefaultController::className(), 'Login'); ?></h1>
    </div>
    <div class="col-sm-4">
        <?php
        // Form
        $form = \yiingine\widgets\ActiveForm::begin([
            'enableAjaxValidation' => false,
            'enableClientValidation' => false
        ]); ?>
            <?= $form->field($model, 'username', ['inputOptions' => ['class' => 'form-control', 'autofocus' => 'autofocus', 'tabindex' => 1]])->textInput(); ?>
            <?= $form->field($model, 'password')->passwordInput(); ?>
               <?= $form->field($model, 'rememberMe', ['parts' => ['{input}', '{label}']])->checkBox(); ?>
            <?php if($this->context->module->allowPasswordRecovery): // Link "I forgot my password" ?>
                    <?php echo Html::a(Yii::t(\yiingine\modules\users\controllers\DefaultController::className(), 'I forgot my password'), ['/users/profile/recover'], ['class' => 'text-warning', 'style' => 'float:right;']);?>
            <?php endif; ?>
            <?php // Login button
                echo Html::submitButton(Yii::t(\yiingine\modules\users\controllers\DefaultController::className(), 'Login'), ['class'=>'btn btn-primary']);
                // Link "register"
                if($this->context->module->allowRegistration &&  !Yii::$app->getParameter('yiingine.users.disable_user_registration'))
                {
                    echo Html::a(Yii::t(\yiingine\modules\users\controllers\DefaultController::className(), 'Register'), ['/users/register'], ['class' => 'btn btn-info']);
                }
             ?>
        <?php \yiingine\widgets\ActiveForm::end();?>
    </div>
</div>
