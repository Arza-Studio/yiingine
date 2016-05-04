<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Url;
use \yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;
use \yiingine\modules\users\widgets\LoginBox;

$module = Yii::$app->getModule('users');

# HTML
if(Yii::$app->user->isGuest):
?>
    <button class="btn btn-primary form-group" data-toggle="modal" data-target="#loginBoxModal" role="button" aria-expanded="false" title="<?= Yii::t(get_class($this->context), 'Login'); ?>">
        <span class="visible-xs-block visible-sm-block visible-md-block"><?= FA::icon('sign-in'); ?></span>
        <span class="visible-lg-block"><?= Yii::t(get_class($this->context), 'Login'); ?></span>
    </button>
<?php else: ?>
    <?php if($switchType === LoginBox::DROPDOWN): ?>
    <div class="dropdown hidden-xs form-group">
        <?= // Logout begin form
        Html::beginForm(Url::to($this->context->logoutUrl), 'post', ['class' => 'logout']);
        ?>
        <?= Html::hiddenInput('LogoutForm[hidden]'); // Just to make sure the LogoutForm form data is present. ?>
        <?= Html::hiddenInput('LogoutForm[returnUrl]', $this->context->returnLogoutUrl === null ? Yii::$app->request->url : $this->context->returnLogoutUrl); ?>
        <button class="btn btn-primary" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="<?php echo Yii::$app->user->getIdentity()->username; ?>">
            <span class="visible-xs-block visible-sm-block"><?= FA::icon('user'); ?></span>
            <span class="visible-md-block visible-lg-block"><?= Yii::$app->user->getIdentity()->username; ?></span>
        </button>
        <ul class="dropdown-menu">
            <?php if(Yii::$app->user->getIdentity()->superuser): // Display a link to the administration interface if the use is an administrator. ?>
                <li><?= Html::a(Yii::t(get_class($this->context), 'Administration'), Url::to(['/admin']), ['title' => Yii::t(get_class($this->context), 'Go to admin home panel')]); ?></li>
                <li class="divider"></li>
            <?php endif; ?>
            <?php // Profile link
            if($module->allowProfileEdition || $module->allowPublicProfiles):
            $profileText = ($this->context->profileText === null) ? ($module->allowPublicProfiles ? Yii::t(get_class($this->context), 'View my account') : Yii::t(get_class($this->context), 'Edit my account')) : $this->context->profileText;
            ?>
                <li class="<?= Url::to(['/users/profile']) == Yii::$app->request->url ? ' active' : '' ?>">
                    <?= Html::a($profileText, Url::to([$module->allowPublicProfiles ? '/users/profile/index': '/users/profile/edit']), ['title' => ($module->allowPublicProfiles ? Yii::t(get_class($this->context), 'View my account') : Yii::t(get_class($this->context), 'Edit my account'))]); ?>
                </li>
            <?php endif; ?>
            <li>
                <?php // Logout link
                $logoutText = ($this->context->logoutText === null) ? Yii::t(get_class($this->context), 'Logout') : $this->context->logoutText;
                echo Html::a('<b>'.$logoutText.'</b>', '#', ['title' => Yii::t(get_class($this->context), 'Logout'), 'onclick' => '$("form.logout").submit(); return false;']);
                ?>
            </li>
        </ul>
        <?= Html::endForm(); ?>
    </div>
    <?php endif; ?>
    <button class="btn btn-primary<?php if($switchType !== LoginBox::MODAL) echo ' visible-xs form-group'; ?>" data-toggle="modal" data-target="#loginBoxModal" role="button" aria-expanded="false" title="<?php echo Yii::$app->user->getIdentity()->username; ?>">
        <span class="visible-xs-block visible-sm-block"><?= FA::icon('user'); ?></span>
        <span class="visible-md-block visible-lg-block"><?= Yii::$app->user->getIdentity()->username; ?></span>
    </button>
<?php endif; ?>
