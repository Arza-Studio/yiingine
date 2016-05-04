<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\tasks;

use Yii;
use yiingine\models\TaskReport;
use DateTime;

/**
 * Task is a base class for maintenance tasks of the web application. 
 * To indicate a component is a task, it must inherit from this widget 
 * and its name must end with "Task".
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
abstract class Task extends \yii\base\Object
{
    //Task statuses.
    const STATUS_FAILED = 0;
    const STATUS_DONE = 1;
    const STATUS_DONE_WITH_WARNINGS = 2;
    const STATUS_OVERDUE = 3;
    const STATUS_UNKNOWN = 4;
    
    //Task intervals.
    const ON_DEMAND = 0;
    const HOURLY = 1;
    const DAILY = 2;
    const MONTHLY = 3;
    const YEARLY = 4;
    const WEEKLY = 5;
    
    /**
     * @var integer the result of the last run.
     */
    private $_status = self::STATUS_UNKNOWN;
    
    /**
     * @var TaskReport the last report for this task.
     * */
    private $_report;
    
    /** @var string the name of the task.*/
    public $name;
    
    /** @var string the identifier for the task.*/
    public $taskId;
    
    /** @var string a description of the task.*/
    public $description;
    
    /**
     * @var array a list of errors generated during the last run.
     */
    public $errors = [];
    
    /**
     * @var array a list of warnings generated during the last run.
     */
    public $warnings = [];

    /**
     * @var array a list of reports generated during the last run.
     */
    public $reports = [];
    
    /**@var integer the interval code for each run.*/
    public $interval; 
    
    /** @var boolean if the task is enabled.*/
    public $enabled = true;
    
    /**
     * @var boolean if the task can only be run in console mode.
     * */
    public $consoleOnly = false;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        /* Check if the needed attributes are set. If not, an Exception will be thrown by
        the framework. */
        if(!isset($this->name)){ throw new \yii\base\Exception('"name" not set');}
        if(!isset($this->description)){ throw new \yii\base\Exception('"interval" not set'); }
        if(!isset($this->taskId)){ throw new \yii\base\Exception('"taskId" not set'); }
        if(!isset($this->interval)){ throw new \yii\base\Exception('"interval" not set'); }
        
        $this->getStatus(); // Gets the status for this task.
    }
    
    /**
     * Computes and returns the status of the task, ie: if it is overdue, has failed, etc.
     * @return integer the status of the task.
     */
    public function getStatus()
    {
        // Gets the task report where the last run date is stored.
        $this->_report = isset($this->_report) ? $this->_report : TaskReport::find()->where(['task_id' => $this->taskId])->one();
        
        if($this->_report) //If there is a report for this task.
        {
            $date = new DateTime($this->_report->execution_date); //Get the date the task was las ran.
            
            //Check if task is overdue by comparing its last run date with its interval.
            $diff = $date->diff(new DateTime());
            switch($this->interval)
            {
                case self::HOURLY:
                    $overdue = $diff->h >= 1;
                    break;
                case self::DAILY:
                    $overdue = $diff->d >= 1;
                    break;
                case self::WEEKLY:
                    $overdue = $diff->d >= 7;
                    break;
                case self::MONTHLY:
                    $overdue = $diff->m >= 1;
                    break;
                default:
                    $overdue = false;
            }
            
            //If the task is overdue change its status.
            $this->_status = $overdue ? TaskReport::STATUS_OVERDUE: $this->_report->status;
        }
        else //The task has never been ran.
        {
            $this->_status = TaskReport::STATUS_OVERDUE; //Report it as overdue.
        }
        
        return $this->_status;
    }
    
    /**
     * Runs the task and report on its result.
     */
    public final function run()
    {
        if($this->consoleOnly && Yii::$app instanceof \yiingine\web\Application)
        {
            return; // This task can only be run in console mode.
        }
        
        try
        {
            $this->runTask();
        }
        catch(\yii\base\Exception $e) //Catches any unhandled exception.
        {
            if(YII_DEBUG) //If YII is in debug mode.
            {
                throw $e; //Do not handle exception.
            }
            // Report it.
            $this->errors[]= 'Task threw '.get_class($e).' '.$e->getCode().' "'.$e->getMessage().'" at line '.$e->getLine().' in file '.$e->getFile();
        }
        
        // If there are errors, the task failed.
        if(count($this->errors)) { $this->_status = TaskReport::STATUS_FAILED; }
        //If there are warnings the task was done with warnings.
        else if(count($this->warnings)) { $this->_status = TaskReport::STATUS_DONE_WITH_WARNINGS; }
        else{ $this->_status = TaskReport::STATUS_DONE; } //Task ran correctly.
        
        $this->generateReport(); //Generate a report for the task.
    }
    
    /** Used internally to run tasks. */
    protected abstract function runTask();
    
    /**Generate a report for this task using what is contained in warnings, errors
     * and status.*/
    protected function generateReport()
    {
        $model = new TaskReport();
        $model->task_id = $this->taskId;
        $model->status = $this->_status;
        $report = '';
        
        //Print all errors to the report.
        foreach($this->errors as $error)
        {
            $report .= 'Error: '.$error."\n";
        }
        
        //Print all warnings to the report.
        foreach($this->warnings as $warning)
        {
            $report .= 'Warning: '.$warning."\n";
        }
        
        //Print all reports to the report.
        foreach($this->reports as $r)
        {
            $report .= 'Report: '.$r."\n";
        }
        
        $model->report = $report;
        
        if(!$model->save()) //If saving failed.
        {
            $this->_status = TaskReport::STATUS_FAILED; //Report it as an error. 
            $this->errors[] = 'Could not save task report';
        }
        
        $this->_report = $model; //Save the last report.
    }
    
    /** This function checks both the attribute and the configuration entries to see if the 
     * task is enabled.
     * @return boolean if the task is enabled.*/
    public function getEnabled()
    {
        // The attribute has precedence of the configuration entry.
        if($this->enabled && isset(\Yii::$app->params[$this->taskId.'.enabled']))
        {
            return \Yii::$app->params[$this->taskId.'.enabled'];
        }
        
        return $this->enabled;
    }
    
    /**
     * Return an array of all the loaded tasks. The detection process starts
     * at the application level, and then scans modules.
     * @return array objects for every tasks loaded. To indicate a component is
     * a task, it must inherit from this class and its name must end with "Task".
     */
    public static function getTasks()
    {
        return self::_getTasksInternal(\Yii::$app);
    }
    
    private static function _getTasksInternal($module)
    {
        $tasks = []; // Will contain the tasks found.
        
        $t = $module->getComponents(false);
        
        // Iterates through all components of this module.
        foreach($module->getComponents(true) as $name => $component)
        {
            /* Components configuration that override that of the yiingine in
             * the client site configuration will be incomplete so we should skip them.*/
            if(is_array($component) && !isset($component['class'])) { continue; }
            
            $component = $module->$name;
            
            // Detects if the component is a task and it is enabled.
            if($component instanceof self && $component->getEnabled())
            {
                $tasks[$component->taskId] = $component; //This components is a task.
            }
        }
        
        // Make a recursive call to all children modules.
        foreach($module->modules as $name => $config)
        {
            $tasks = array_merge($tasks, self::_getTasksInternal($module->getModule($name)));
        }

        return $tasks;
    }
    
    /**
     * Get the number of tasks requiring the user's attention.
     * @return integer the number of pending tasks.
     */
    public static function getPendingTasks()
    {
        $pendingTasks = 0;
        
        //Iterates through each task.
        foreach(self::getTasks() as $task)
        {
            //If the task is not done, this means it is pending.
            if($task->status != self::STATUS_DONE)
            {
                $pendingTasks++; 
            }
        }
        
        return $pendingTasks;
    }
}
