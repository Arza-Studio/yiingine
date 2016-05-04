<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Url;
use \yiingine\models\TaskReport;
use \yiingine\models\ActiveRecordLogEntry;

$this->params['breadcrumbs'] = [Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Administration Panel')];

$bundle = \yiingine\assets\admin\AdminAsset::register($this);

$this->title = Yii::$app->name.' - '.Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Administration Panel');

$tasks = \yiingine\tasks\Task::getTasks();

$user = Yii::$app->user->getIdentity();

?>
<div class="jumbotron">
    <h2>Hello <?= $user->username; ?>! Welcome to the administration panel!</h2>
    <p>Currently using the yiingine version <?php echo YIINGINE_VERSION; ?></p>
    <?php if(Yii::$app->getParameter('app.log_active_record_changes', true)): 
    
        $currentVisitEntry = ActiveRecordLogEntry::find()->where([
            'model_id' => $user->id, 
            'model_table' => $user->tableName(),
            'attribute' => 'lastvisit',
        ])->andWhere([])->orderBy('datetime DESC')->one();
        
        if($currentVisitEntry)
        {
            $previousVisitEntry = ActiveRecordLogEntry::find()->where([
                'model_id' => $user->id, 
                'model_table' => $user->tableName(),
                'attribute' => 'lastvisit',
            ])->andWhere(['<', 'datetime', $currentVisitEntry->datetime])->orderBy('datetime DESC')->one();
        }
    ?>
        <p>Since your last visit, there has been <?= !$currentVisitEntry || !$previousVisitEntry ? 0: $currentVisitEntry->id - $previousVisitEntry->id - 1;?> operations on the site.</p>
    <?php endif; ?>
</div>
<div class="col-lg-4">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><a href="<?= Url::to(['tasks'])?>"><?= Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Tasks'); ?></a></h3>
        </div>
        <div class="panel-body">
            <ul>
            <?php foreach($tasks as $task): 
                switch($task->status)
                {
                    case TaskReport::STATUS_FAILED: $class = 'danger'; break;
                    case TaskReport::STATUS_DONE: $class = 'success'; break;
                    case TaskReport::STATUS_DONE_WITH_WARNINGS: $class = 'primary'; break;
                    case TaskReport::STATUS_OVERDUE: $class = 'warning'; break;
                    default: $class = 'primary';
                }
            ?>
                <li class="text-<?= $class; ?>"><?= $task->name.' ('.TaskReport::getStatusName($task->status).')'?></li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php foreach(Yii::$app->modules as $module):
    if(!($module instanceof \yiingine\base\Module))
    {
        continue;
    }
?>
    <div class="col-lg-4">
        <?= $module->getAdminPanel(); ?>
    </div>
<?php endforeach; ?>
