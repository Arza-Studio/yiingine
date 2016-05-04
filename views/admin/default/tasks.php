<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\models\TaskReport;
use \yii\helpers\Html;

$this->params['breadcrumbs'][] = Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Tasks');

$taskIds = [];

foreach($tasks as $id => $task) // Display a description of each task.
{
    $taskIds[$id] = $task->name;
}

$columns = [
    [
        'attribute' => 'id',
        'headerOptions' => ['width' => '30', 'style' => 'padding-right:0px;'],
        'options' => ['style' => 'color:#7f7f7f;'],
    ],
    [
        'attribute' => 'task_id',
        'headerOptions' => ['width' => '100'],
        'filter' => $taskIds,
        'value' => function ($model, $key, $index, $column)
        {
            static $tasks = null; // To avoid fecthing tasks for each report.
            
            if($tasks === null)
            {
                $tasks = \yiingine\tasks\Task::getTasks();
            }
            
            return isset($tasks[$model->task_id])? $tasks[$model->task_id]->name: $model->task_id;
        },
    ],
    [
        'attribute' => 'status',
        'filter' => [
            TaskReport::STATUS_FAILED => TaskReport::getStatusName(TaskReport::STATUS_FAILED),
            TaskReport::STATUS_DONE => TaskReport::getStatusName(TaskReport::STATUS_DONE),
            TaskReport::STATUS_DONE_WITH_WARNINGS => TaskReport::getStatusName(TaskReport::STATUS_DONE_WITH_WARNINGS),
            TaskReport::STATUS_OVERDUE => TaskReport::getStatusName(TaskReport::STATUS_OVERDUE),
            TaskReport::STATUS_UNKNOWN => TaskReport::getStatusName(TaskReport::STATUS_UNKNOWN),
        ],
        'filterInputOptions' => [ 'prompt' => '', 'class' => 'form-control'],
        'headerOptions' => ['width'=>'100'],
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column)
        {        
            switch($model->status)
            {
                case TaskReport::STATUS_FAILED: $class = 'text-danger'; break;
                case TaskReport::STATUS_DONE: $class = 'text-success'; break;
                case TaskReport::STATUS_DONE_WITH_WARNINGS: $class = 'text-warning'; break;
                default: $class = 'text-primary';
            }
            return Html::tag('span', TaskReport::getStatusName($model->status), ['class' => $class]);
        },
    ],
    [
        'attribute' => 'report',
        'headerOptions' => ['width' => '250'],
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column)
        {
            switch($model->status)
            {
                case TaskReport::STATUS_FAILED: $class = 'text-danger'; break;
                case TaskReport::STATUS_DONE: $class = 'text-success'; break;
                case TaskReport::STATUS_DONE_WITH_WARNINGS: $class = 'text-warning'; break;
                default: $class = 'text-primary';
            }
            return Html::tag('span', $model->report, ['class' => $class]);
        },
    ],
    [
        'attribute' => 'execution_date',
        'headerOptions' => ['width' => '150'],
    ]
];

//Appends a Run action to the center buttons of the action bar.
$form = Html::beginForm([''], 'post');
$form .= Html::hiddenInput('runTasks', 1);
$form .= \yii\helpers\Html::submitButton(Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Run'), [
    'class' => 'btn btn-primary',
    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Run all tasks')
]);
$form .= Html::endForm();

$this->params['centerButtons'][] = $form;

?>
<div class="list-group">
<?php foreach($tasks as $id => $task): 
    switch($task->status)
    {
        case TaskReport::STATUS_FAILED: $class = 'danger'; break;
        case TaskReport::STATUS_DONE: $class = 'success'; break;
        case TaskReport::STATUS_DONE_WITH_WARNINGS: $class = 'primary'; break;
        case TaskReport::STATUS_OVERDUE: $class = 'warning'; break;
        default: $class = 'primary';
    }
?>
    <div class="list-group-item">
        <h3 class="list-group-item-heading text-<?= $class; ?>"><?= $task->name.' ('.TaskReport::getStatusName($task->status).($task->consoleOnly ? ' [Console]' : '').')'; ?></h3>
        <div class="list-grou-item-text"><?= $task->description; ?></div>
        <?php if($task->errors || $task->warnings): ?>
            <?php if($task->errors):?>
                <div class="text-danger">
                    <h4><?= Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Errors'); ?></h4>
                    <?php foreach($task->errors as $error) echo $error.'<br />'; ?>
                </div>
            <?php endif; ?>
            <?php if($task->warnings): ?>
                <div class="text-warning">
                    <h4><?= Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Warnings'); ?></h4>
                    <?php foreach($task->warnings as $warning) echo $warning.'<br />'; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>

<?php echo \yii\grid\GridView::widget([
    'id' => $model->formName().'-grid',
    'dataProvider' => $model->search(Yii::$app->request->queryParams),
    'filterModel' => $model,
    //'enableHistory' => true,
    'columns' => $columns,
    'options' => ['class' => 'table table-striped table-hover'],
    'summaryOptions' => ['style' => 'float:right;'],
]);
