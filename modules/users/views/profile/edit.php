<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;

$this->title = Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Profile');

$this->params['breadcrumbs'] = [
    ['url' => ['index'], 'label' => $model->username],
    $model->isNewRecord ? Yii::t('generic', 'Create') : Yii::t('generic', 'Edit')
];

?>

<h1 class="page-header"><?= $this->title; ?></h1>
        <?php 
        $activeForm = \yiingine\widgets\ActiveForm::begin([
            'id' => $model->formName().'-form',
            'enableAjaxValidation' => false,
            'enableClientValidation' => false,
            'errorSummaryCssClass' => 'alert alert-danger'
        ]);
        
        echo $activeForm->errorSummary($model);
        ?>
            <section>
                <?php
                    $structure = $this->requireFile('@app/modules/users/views/_forms/_user.php', ['model' => $model]);
                    echo $activeForm->formStructure($model, $structure); 
                ?>
                <?= \yiingine\widgets\RequiredFieldsNote::widget(); ?>
                <input type="submit" class="btn btn-primary" value="<?= Yii::t('generic', 'Save'); ?>" />
                <?= Html::a(Yii::t('generic', 'Back'), ['index'], ['class' => 'btn btn-default']);?>
                <?php 
                    // If an account deletion button should be displayed.
                    if(Yii::$app->getModule('users')->allowAccountDeletion && !$model->isNewRecord && !$model->superuser)
                    {
                        echo Html::a(Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Delete account'), ['delete'], [
                                'onclick' => 'return confirm("'.Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Account deletion is permanent, are you sure you want to proceed?').'")',
                                'class' => 'btn btn-danger',
                                'style' => 'float: right;'
                            ]
                        );
                     }
                 ?>
            </section>
        <?php \yiingine\widgets\ActiveForm::end();?>
