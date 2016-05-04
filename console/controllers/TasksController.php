<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\console\controllers;

use \Yii;
use \yii\helpers\Console;
use \yii\helpers\FileHelper;
use \yiingine\models\TaskReport;
use \yiingine\tasks\Task;

/**This class contains commands for running Yiingine tasks. Please refer to the documentation of
 * the Yiingine for instructions on how to configure a web server to automatically run those tasks.*/
class TasksController extends \yii\console\Controller
{     
    /** @var array the tasks. */
    protected $tasks;
    
    /** @var sting the default action. */
    public $defaultAction = 'all';
    
    /** Initialize the controller .*/
    public function init()
    {
        Yii::$app->language = Yii::$app->sourceLanguage; // Force the language back to the source language.
        
        parent::init();
    }
    
    /** 
     * @inheritdoc
     * */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) 
        {
            return false;
        }
    
        echo 'Fetching configuration entries...';
        
        // Foreach configuration entry defined in database.
        foreach(\yiingine\models\ConfigEntry::find()->all() as $configEntry)
        {
            // Add it in the application parameters.
            Yii::$app->params[$configEntry->name] = $configEntry->value;
        }
        
        echo '[DONE]'."\n";
        
        echo 'Retrieving tasks...';
        $this->tasks = Task::getTasks(); // Retrieve all tasks.
        echo '[DONE]'."\n";
    
        return true;
    }
    
    /** This action runs all tasks.
     * @param boolean $force forces all tasks to run.
     * */
    public function actionAll($force = false)
    {
        foreach($this->tasks as $task) // Iterates through all tasks.
        {
            // If the status of the task is not DONE or DONE_WITH_WARNINGS.
            if(($task->getStatus() != TaskReport::STATUS_DONE && 
                $task->getStatus() != TaskReport::STATUS_DONE_WITH_WARNINGS) ||
                $force)
            {
                echo 'Running task '.$task->taskId.'...'; 
                $task->run(); //Run this task.
                echo '['.mb_strtoupper(TaskReport::getStatusName($task->getStatus())).']'."\n";
                foreach($task->errors as $error) //Iterate through all errors.
                {
                    echo '   Error: '. $error."\n"; //Print them.
                }
                foreach($task->warnings as $warning) //Iterate through all warnings.
                {
                    echo '   Warning: '. $warning."\n"; //Print them.
                }
            }
        }
        
        echo 'Done runnings all tasks.'."\n";
    }
    
    /**This action lists all available tasks.*/
    public function actionList()
    {
        if(!$this->tasks) // If there are no tasks.
        {
            echo "    No tasks found\n";
            return; 
        }
        
        foreach($this->tasks as $task) //Iterate through all tasks.
        {
            echo '   '; //Indent.
            //Echo the name of the task followed by its status.
            echo $task->taskId.' : '.TaskReport::getStatusName($task->getStatus())."\n";
        }    
    }
}
