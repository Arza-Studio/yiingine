<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$this->title = Yii::t(\yiingine\modules\users\controllers\RegisterController::className(), 'Registration') ;

$this->params['breadcrumbs'][] = $this->title;
?>
<h2><?= $this->title; ?></h2>

<?php 
$activeForm = \yiingine\widgets\ActiveForm::begin([
    'id' => $model->formName().'-form',
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
    'errorSummaryCssClass' => 'alert alert-danger'
]);

echo $activeForm->errorSummary($model);
?>
        <?php
            $structure = $this->requireFile('@app/modules/users/views/_forms/_user.php', ['model' => $model]);
            
            if(Yii::$app->controller->module->doCaptchaAtRegistration)
            {
                $structure['elements'][] = [
                    'type' => 'fieldset',
                    'elements' => [
                        'captcha' => ['type' =>'\yiingine\widgets\Captcha']
                    ]
                ]; 
            }
            echo $activeForm->formStructure($model, $structure); 
        ?>
        <?= \yiingine\widgets\RequiredFieldsNote::widget(); ?>
        <input type="submit" class="btn btn-primary" value="<?= Yii::t(\yiingine\modules\users\controllers\RegisterController::className(), 'Register'); ?>" />
<?php \yiingine\widgets\ActiveForm::end();?>
