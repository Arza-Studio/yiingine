<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;

$this->params['breadcrumbs'][] = ['label' => $model->getModelLabel(), 'url' => ['index']];

// Only add buttons if this is not an ajax request and the model is not singleton.
if(!Yii::$app->request->isAjax && !$this->context->singleton)
{
    $queryParams = Yii::$app->request->queryParams;
    unset($queryParams['id']);
    
    // Appends a Manage action to the right buttons of the action bar.
    $this->params['rightButtons'][] = Html::a(FA::icon('table'), $this->context->action->id == 'index' ? ['index'] : array_merge(['index'], $queryParams), [
        'class' => 'btn btn-primary'.($this->context->action->id == 'index' ? ' locked': ''),
        'title' => Yii::t(\yiingine\web\admin\ModelController::className(), 'Update, search and delete {model}', ['model' => $model->getModelLabel(true)])
    ]);

    // If a record of this model can be created and if the user has the permissions to do so.
    if($this->context->allowCreate && $this->context->checkAccess('create')) 
    {
        // Appends a create action to the right buttons of the action bar.
        $this->params['rightButtons'][] = Html::a(FA::icon('plus'), array_merge(['create'], $queryParams), [
            'class' => 'btn btn-primary'.($this->context->action->id == 'create' ? ' disabled': ''),
            'title' => Yii::t(\yiingine\web\admin\ModelController::className(), 'Create a new {model}', ['model' => $model->getModelLabel()]),
        ]);
    }
}
