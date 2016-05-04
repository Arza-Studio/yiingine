<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;

$class = $model->formName();

// If the user is allowed to save a form.
if($this->context instanceof \yiingine\web\admin\ModelController && ($this->context->checkAccess('update') || $this->context->checkAccess('create')))
{   
    $advancedDisplay = $this->context->adminDisplayMode === \yiingine\models\Admin\AdminParameters::ADVANCED_DISPLAY_MODE;
    
    // Add a "Cancel" button on the left buttons.
    $this->params['leftButtons'][] = Html::a(FA::icon('eraser'), array_merge(['index'], Yii::$app->request->queryParams), [
        'class' => 'btn btn-primary',
        'title' => Yii::t(\yiingine\web\admin\ModelController::className(), 'Cancel changes and go back to the manage page'),
    ]);
    
    $this->params['centerButtons'][] = Html::beginTag('div', ['class' => 'btn-group']);
    
    // Add a "save" button to the center buttons.
    $this->params['centerButtons'][] = Html::a(!$model->isNewRecord ? Yii::t(\yiingine\web\admin\ModelController::className(), 'Update'): Yii::t(\yiingine\web\admin\ModelController::className(), 'Create'), '', [
        'class' => 'btn btn-primary',
        'title' => Yii::t(\yiingine\web\admin\ModelController::className(), 'Save record to the database'),
        // Submit and remove the window.onbeforeunloadevent because the user has clicked submit.
        'onclick' => "window.onbeforeunload = null;$('#main-form').find('form').submit();return false;",
        'id' => $class.'-update-create'
    ]);
    
    $viewable = $model instanceof \yiingine\db\ViewableInterface && $model->getUrl() !== false;
    
    /* If the model was reached from the site through an AdminOverlay widget and the url was different
     * than the default url for this model.*/
    if(Yii::$app->request->get('returnUrl') && ((!$viewable || \yii\helpers\Url::to($model->getUrl()) != urldecode(Yii::$app->request->get('returnUrl')))))
    {
        // Add a "save and view in site" button in the center buttons.
        $this->params['centerButtons'][] = Html::a(FA::icon('arrow-right').FA::icon('reply'), '', [
            'class' => 'btn btn-primary',
            'title' => Yii::t(\yiingine\web\admin\ModelController::className(), 'Save record to the database and return to previous page'),
            // Submit and remove the window.onbeforeunloadevent because the user has clicked submit.
            'onclick' => "$('#redirectionActionOnSuccess').attr('value', 'overlay');window.onbeforeunload = null;$('#main-form').find('form').submit();return false;",
            'id' => $class.'-save-return'
        ]);
    }
    else if($viewable) // If the model can be viewed in site.
    {
        // Add a "save and view in site" button in the center buttons.
        $this->params['centerButtons'][] = Html::a(FA::icon('arrow-right').FA::icon('dot-circle-o'), '', [
            'class' => 'btn btn-primary',
            'title' => Yii::t(\yiingine\web\admin\ModelController::className(), 'Save record to the database and view in site'),
            // Submit and remove the window.onbeforeunloadevent because the user has clicked submit.
            'onclick' => "$('#redirectionActionOnSuccess').attr('value', 'site');window.onbeforeunload = null;$('#main-form').find('form').submit(); return false;",
            'id' => $class.'-save-return'
        ]);
    }
    
    if(!$this->context->singleton)
    {
        // Add a "save and continue to edit (return to the form)" button on the center buttons.
        $this->params['centerButtons'][] = Html::a(FA::icon('arrow-right').FA::icon('pencil'), '', [
            'class' => 'btn btn-primary',
            'title' => Yii::t(\yiingine\web\admin\ModelController::className(), 'Save record to the database and continue editing'),
            // Submit and remove the window.onbeforeunloadevent because the user has clicked submit.
            'onclick' => "$('#redirectionActionOnSuccess').attr('value', 'form');window.onbeforeunload = null;$('#main-form').find('form').submit(); return false;",
            'id' => $class.'-save-form'
        ]);
        
        if($this->context->allowCreate && $this->context->allowCopy && !$model->isNewRecord && Yii::$app->user->can($class.'-create'))
        {
            // Add a "save and create a new model by copy" button on the center buttons.
            $this->params['centerButtons'][] = Html::a(FA::icon('arrow-right').FA::icon('copy'), '', [
                'class' => 'btn btn-primary',
                'title' => Yii::t(\yiingine\web\admin\ModelController::className(), 'Save record to the database and create a copy'),
                // Submit and remove the window.onbeforeunloadevent because the user has clicked submit.
                'onclick' => "$('#redirectionActionOnSuccess').attr('value', 'copy');window.onbeforeunload = null;$('#main-form').find('form').submit(); return false;",
                'id' => $class.'-save-copy'
            ]);
        }
    }
    
    $this->params['centerButtons'][] = Html::endTag('div');
}
else if($this->context instanceof \yiingine\web\admin\ModelController)
{
    $queryParams = Yii::$app->request->queryParams;
    unset($queryParams['id']);
    
    // Add a "Return" button to the center buttons.
    $this->params['centerButtons'][] = Html::a(Yii::t(\yiingine\web\admin\ModelController::className(), 'Return'), array_merge(['index'], $queryParams), [
        'class' => 'btn btn-primary',
        'title' => Yii::t(\yiingine\web\admin\ModelController::className(), 'Return to the index page'),
    ]);
}

// For some reason, the isAjaxRequest parameter does not always work.
if(!Yii::$app->request->isAjax && $this->context->layout != '//layouts/adminFormOnly')
{   
    // Register a script to lock the menu item that is currently being visited.
    $jsReady = 'lockMenuItems("adminMenu"';
    $jsReady .= ', "'.\yii\helpers\Url::to(array_merge(['/'.$this->context->uniqueId.'/index'], $this->context->actionParams)).'"+(window.location.hash ? window.location.hash : "")';
    $jsReady .= ', "'.Yii::$app->request->baseUrl.'"';
    $jsReady .= ', "'.Yii::$app->language.'"';
    $jsReady .= ', "'.\yii\helpers\Url::to(['/']).'"';
    $jsReady .= ', function(){ initializeAdminMenu(); }';
    $jsReady .= ');';
    \yii\web\View::registerJs($jsReady, \yii\web\View::POS_READY);
}

if($model->hasErrors('lastUpdateTime'))
{
    $error =  $model->getErrors('lastUpdateTime');
    Yii::$app->user->setFlash(\yiingine\widgets\FlashMessage::DANGER, $error[0]);
} 
?>
<div id="main-form" class="container-fluid">
    <div class="row">
        <?php 
        $activeForm = \yiingine\widgets\ActiveForm::begin([
            'id' => $model->formName().'-form',
            'enableAjaxValidation' => false,
            'enableClientValidation' => false,
            'errorSummaryCssClass' => 'alert alert-danger'
        ]);
        
        // Stores the type of redirection to occur after a form submission and redirect. Will be set by Javascript.
        echo \yii\helpers\Html::hiddenInput('redirectionActionOnSuccess', '', ['id' => 'redirectionActionOnSuccess']);
        
        echo $activeForm->errorSummary($model);
        
        if(!isset($form['type'])): // If the form has levels.
        ?>
            <section class="col-sm-8 col-lg-9">
                <?php
                // The first form level gets displayed in the center.
                echo $activeForm->formStructure($model, ['type' => 'form', 'elements' => [$form[1]]], 1); 
                ?>
                <?= \yiingine\widgets\RequiredFieldsNote::widget(); ?>
            </section>
            <aside class="col-sm-4 col-lg-3">
                <?php 
                    // Display the remaining levels in the aside column.
                    for($i = 2; $i < count($form) + 1; $i++)
                    {
                        echo $activeForm->formStructure($model, ['type' => 'form', 'elements' => $form[$i]], 1);
                    } 
                ?>
            </aside>
        <?php else: ?>
            <section>
                <?php echo $activeForm->formStructure($model, $form, 1); ?>
                <?= \yiingine\widgets\RequiredFieldsNote::widget(); ?>
            </section>
        <?php 
            endif;
            \yiingine\widgets\ActiveForm::end();
        ?>
    </div>
</div>
