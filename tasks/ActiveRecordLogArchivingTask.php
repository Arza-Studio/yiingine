<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\tasks;

use Yii;
use yiingine\models\ActiveRecordLogEntry;
use DateTime;

/**
 * A task for archiving the active record log when it gest too long.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class ActiveRecordLogArchivingTask extends Task
{    
    /**@var string the directory the backups go to.*/
    public $directory;
    
    /**@var string the path to the socket if the dump command should connect trough it.
     * Ignored if the database connection is made through tcp/ip.*/
    public $socket = '';
    
    /** @var integer the size at which the active record should be archived.*/
    public $size = 2000;
    
    /**
     * @see CWidget::init()
     */
    public function init()
    {               
        $this->consoleOnly = true; // This task could take too much time to run using the web interface.
        
        if(!isset($this->directory)) //If there is not backup directory set.
        {
            //Set one by default.
            $this->directory = Yii::getAlias('@app/runtime');
        }
        
        //Translates the name and description.
        $this->description = Yii::t(__CLASS__, 'Archives parts of the ActiveRecord log to {dir} when its size gets over {size}.', ['dir' => $this->directory, 'size' => $this->size]);
        $this->name = Yii::t(__CLASS__, 'ActiveRecord Log Archiving');
        $this->taskId = 'ActiveRecordLogArchivingTask';
        $this->interval = self::DAILY;
        
        parent::init(); //Calls the parent.
    }
    
    /**
     * Runs the task.
     */
    protected function runTask()
    {          
        if(Yii::$app->getParameter('app.log_active_record_changes')) // If logging is disabled. 
        {
            $this->errors[] = 'ActiveRecord logging is disabled.';
            return;
        }
        
        if(!file_exists($this->directory)) // If the directory does not exist.
        {
            if(!mkdir($this->directory, 0774, true)) // Create it.
            {
                // Report it as an error.
                $this->errors[] = 'Could not create archiving directory at '.$this->directory;
                return;
            }
        }
        
        // If the size of the log is under the archiving threshold.
        if(ActiveRecordLogEntry::find()->count() < $this->size)
        {
            return;
        }
        
        // Grab the last entry of the backup.
        $last = ActiveRecordLogEntry::find()->orderBy('datetime ASC')->one();
        
        // Grab the first entry of the backup.
        $first = ActiveRecordLogEntry::find()->orderBy('datetime DESC')->one();
        
        // Build the file name.
        $dateLast = new DateTime($last->datetime);
        $dateFirst = new DateTime($first->datetime); 
        $filePath = $this->directory.DIRECTORY_SEPARATOR.'ActiveRecordLog-'.$dateLast->format('YmdHis').'_to_'.$dateFirst->format('YmdHis').'.sql';
        
        $connection = explode(':', Yii::$app->db->connectionString);
        $params = explode(';', $connection[1]); //Get the connection parameters.
        
        //Splits the parameters into name/values pairs.
        foreach($params as $param)
        {
            $kv = explode('=', $param);
            $params[$kv[0]] = $kv[1];
        }
        
        if(!isset($params['socket']) && $this->socket) // If a socket is not defined but should be used.
        {
            $params['socket'] = $this->socket;
        }
        
        switch($connection[0]) //Switch according to the database type.
        {
            case 'mysql': //Build the mysqldump command with the parameters of the application.
                /*A mysqldump command uses the following syntax:
                 * mysqldump --host=localhost --port=3306 --socket="/tmp/mysql.sock" --protocol=TCP --user=USER --password=PASSWORD DBNAME TABLENAME --where=condition
                 * */
                $command = DbBackupTask::getDbCommand('mysql', 'mysqldump', $params).' '.$params['dbname'].' '.ActiveRecordLogEntry::tableName().' --where="id<='.$first->id.'"';
                break;
            default:
                throw new \yii\base\Exception('Database type not supported.');
        }
        
        $return = null; //The return of the command.
        $output = ''; //The output of the command.
        exec($command, $output, $return); //Calls the command.
        
        // Dump the database and save it into a file.
        file_put_contents($filePath, implode("\n", $output));

        if(!file_exists($filePath)) //If the archive file was not created.
        {
            $this->errors[] = 'Could not save archive file, please check the permissions for the archiving directory.';
            return;
        }
        
        if(!filesize($filePath)) //If the file has a size of 0.
        {
            $this->errors[] = 'Archive file has a size of 0.';
        }
        
        // Remove the archived log entries.
        $query = new DbQuery();
        $query->where(['<=', 'datetime', $first->datetime]);
        $query->where(['>=', 'datetime', $last->datetime]);
        ActiveRecordLogEntry::deleteAll($query);
    }
}
