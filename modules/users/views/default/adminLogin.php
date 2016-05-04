<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use rmrevin\yii\fontawesome\FA;

/* To give itself the best chances of functionning in case of a bug with the site,
 * this page uses a minimal layout. */
$this->context->layout = '@yiingine/views/layouts/main';

$this->title = Yii::t(\yiingine\modules\users\controllers\DefaultController::className(), 'Login').' | '.Yii::$app->name;

\yiingine\assets\common\CommonAsset::register($this);

# BOTS PROTECTION
/* Prevent robots from indexing this page. Since registerMetaTag is called later, we cannot 
 * simply register a meta tag because it will get overriden, this is why the value is replaced
 * from within the parameters.*/
Yii::$app->params['yiingine.SocialMetas.meta_robots'] = 'NOINDEX, NOFOLLOW';    

// A common message is shown if either the username or the password is incorrect to avoid giving clues about the username being correct.
if($model->hasErrors('username') || $model->hasErrors('password'))
{
    $model->clearErrors();
    $model->addError('username', Yii::t(\yiingine\modules\users\controllers\DefaultController::className(), 'Username or password incorrect'));
}

?>
<div id="flash-messages"><?= \yiingine\widgets\FlashMessage::display(); ?></div>
<div class="container" style="margin-top: 100px">
    <div class="col-md-4 col-md-offset-2 hidden-xs hidden-sm" style="font-size:50px; padding-top: 60px"><?= FA::icon('sign-in')->size(FA::SIZE_5X); ?></div>
    <div class="col-md-4" style="<?= $model->hasErrors('username') || $model->hasErrors('password') ?  '"margin-top:-111px;"': ''; ?>">
        <h1><?php echo Yii::t(\yiingine\modules\users\controllers\DefaultController::className(), 'Administration'); ?></h1>
        <p><?php echo Yii::t(\yiingine\modules\users\controllers\DefaultController::className(), 'Please fill out the following form with your login credentials'); ?></p>
        <?php $form = \yii\widgets\ActiveForm::begin([
            'id' => 'login-form',
            'enableAjaxValidation' => false,
            'enableClientValidation' => false
        ]); ?>
            <?= $form->field($model, 'username', ['parts' => ['{label}', '{input}', '{error}'], 'inputOptions' => ['autofocus' => 'autofocus', 'class' => 'form-control', 'tabindex' => 1]]); ?>
            <?= $form->field($model, 'password', ['parts' => ['{label}', '{input}']])->passwordInput(); ?>
            <?= $form->field($model, 'rememberMe', ['parts' => ['{label}', '{input}']])->checkbox(); ?>
            <?= \yii\helpers\Html::submitButton(Yii::t(\yiingine\modules\users\controllers\DefaultController::className(), 'Login'), ['class' => 'btn btn-primary']) ?>
            <?= 
                // Just in case someone ends up on this page unwillingly.
                \yii\helpers\Html::a(Yii::t('generic', 'Back'), Yii::$app->request->referrer ? Yii::$app->request->referrer: ['/'], ['class' => 'btn btn-default']); 
            ?>
        <?php \yii\widgets\ActiveForm::end(); ?>
    </div>
</div>
