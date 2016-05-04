<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Url;
use \yii\helpers\Html;
use \yiingine\widgets\ActiveForm;
use rmrevin\yii\fontawesome\FA;

$module = Yii::$app->getModule('users');

// A common message is shown if either the username or the password is incorrect to avoid giving clues about the username being correct.
if(($model->hasErrors('username') || $model->hasErrors('password')) && Yii::$app->controller->route != 'users/default/login')
{
    $model->clearErrors();
    $model->addError('username', Yii::t(\yiingine\modules\users\widgets\LoginBox::className(), 'Username or password incorrect'));
    $this->registerJs('$("#loginBoxModal").modal("show");', \yii\web\View::POS_READY);
}

# HTML

// If unlogged
if(Yii::$app->user->isGuest):
?>
<div class="modal fade" id="loginBoxModal" tabindex="-1" role="dialog" aria-labelledby="loginBoxModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php // Form
            $form = ActiveForm::begin([
                'enableAjaxValidation' => false,
                'enableClientValidation' => false        
            ]); ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel"><?= FA::icon('sign-in').' '.Yii::t(\yiingine\modules\users\widgets\LoginBox::className(), 'Login'); ?></h4>
                </div>
                <div class="modal-body">
                    <?= $form->field($model, 'username', ['inputOptions' => $this->context->usernameHtmlOptions])->label($this->context->usernameLabel)->textInput(); ?>
                    <?= $form->field($model, 'password', ['inputOptions' => $this->context->passwordHtmlOptions])->label($this->context->passwordLabel)->passwordInput(); ?>
                    <?php // Remember me
                    if($this->context->enableRememberMe): ?>
                    <?= $form->field($model, 'rememberMe', ['parts' => ['{input}', '{label}']])->checkBox(); ?>
                    <?php endif; ?>
                    <?php // Link "I forgot my password"
                    if($this->context->forgotPasswordUrl && $module->allowPasswordRecovery): ?>
                    <?php
                    $forgotPasswordText = ($this->context->forgotPasswordText === null) ? Yii::t(\yiingine\modules\users\widgets\LoginBox::className(), 'I forgot my password') : $this->context->forgotPasswordText;
                    echo Html::a($forgotPasswordText, $this->context->forgotPasswordUrl, ['class' => 'text-warning']);
                    ?>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <div class="pull-left">
                    <?php
                    // Link "register"
                    if($this->context->registerUrl && $module->allowRegistration && !Yii::$app->getParameter('yiingine.users.disable_user_registration'))
                    {
                        $locked = (Url::to($this->context->registerUrl) == Yii::$app->request->url) ? ' locked' : '';
                        $registerText = ($this->context->registerText === null) ? Yii::t(\yiingine\modules\users\widgets\LoginBox::className(), 'Register') : $this->context->registerText;
                        echo Html::a($registerText, $this->context->registerUrl, ['class' => 'btn btn-info' . $locked]);
                    }
                    ?>
                    </div>
                    <div class="pull-right">
                    <?php 
                    // Close button
                    echo Html::button(Yii::t('generic', 'Close'), ['class'=>'btn btn-default', 'data-dismiss'=>'modal']);
                    ?>
                    <?php
                    // Login button
                    $loginText = ($this->context->loginText === null) ? Yii::t(\yiingine\modules\users\widgets\LoginBox::className(), 'Login') : $this->context->loginText;
                    echo Html::submitButton($loginText, ['class'=>'btn btn-primary']);
                    ?>
                    </div>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
// If logged
else:
?>
<div class="modal fade" id="loginBoxModal" tabindex="-1" role="dialog" aria-labelledby="loginBoxModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close btn btn-default" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?= FA::icon('user').' '.Yii::$app->user->getIdentity()->username; ?></h4>
            </div>
            <div class="modal-body">
                <?php // Logout begin form 
                
                echo Html::beginForm(Url::to($this->context->logoutUrl), 'post', ['class' => 'logout']);
                ?>
                <?= Html::hiddenInput('LogoutForm[hidden]'); // Just to make sure the LogoutForm form data is present. ?>
                <?= Html::hiddenInput('LogoutForm[returnUrl]', $this->context->returnLogoutUrl === null ? Yii::$app->request->url : $this->context->returnLogoutUrl); ?>
                <?php // Admin button
                if(!Yii::$app->user->isGuest && Yii::$app->user->getIdentity()->superuser):
                    $adminBtnText = ($this->context->adminText === null) ? Yii::t(\yiingine\modules\users\widgets\LoginBox::className(), 'Administration') : $this->context->adminText ;
                    $adminBtnUrl = Url::to(['/users/admin']);
                    $adminBtnClass = 'btn btn-default btn-block';
                    $adminBtnTitle = Yii::t(\yiingine\modules\users\widgets\LoginBox::className(), 'Administration');
                    echo Html::a($adminBtnText, $adminBtnUrl, ['title' => $adminBtnTitle, 'class' => $adminBtnClass]);
                endif; ?>
                <?php // Profile button
                if($module->allowProfileEdition || $module->allowPublicProfiles):
                    $profileBtnText = ($this->context->profileText === null) ? ($module->allowPublicProfiles ? Yii::t(\yiingine\modules\users\widgets\LoginBox::className(), 'View my account') : Yii::t(\yiingine\modules\users\widgets\LoginBox::className(), 'Edit my account')) : $this->context->profileText ;
                    $profileBtnUrl = Url::to(['/users/profile']);
                    $profileBtnClass = 'btn btn-default btn-block';
                    if($profileBtnUrl == Yii::$app->request->url) $profileBtnClass .= ' active';
                    $profileBtnTitle = $module->allowPublicProfiles ? Yii::t(\yiingine\modules\users\widgets\LoginBox::className(), 'View my account') : Yii::t(\yiingine\modules\users\widgets\LoginBox::className(), 'Edit my account');
                    echo Html::a($profileBtnText, $profileBtnUrl, ['title' => $profileBtnTitle, 'class' => $profileBtnClass]);
                endif; ?>
                <?php // Logout button
                $logoutBtnText = ($this->context->logoutText === null) ? Yii::t(\yiingine\modules\users\widgets\LoginBox::className(), 'Logout') : $this->context->logoutText ;
                $logoutBtnUrl = Url::to(['/users/logout']);
                $logoutBtnClass = 'btn btn-warning btn-block';
                $logoutBtnTitle = Yii::t(\yiingine\modules\users\widgets\LoginBox::className(), 'Logout');
                echo Html::button($logoutBtnText, ['title' => $logoutBtnTitle, 'class' => $logoutBtnClass, 'onclick' => '$("form.logout").submit();']);
                echo Html::endForm();
                ?>
            </div>
            <div class="modal-footer">
                <?php 
                // Close button
                echo Html::button(Yii::t('generic', 'Close'), ['class'=>'btn btn-default', 'data-dismiss'=>'modal']);
                ?>
            </div>
        </div>
    </div>
</div>
        
<?php endif; ?>
