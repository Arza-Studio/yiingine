<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\tasks;

use Yii;
use DateTime;

/**
 * A task for automated backups of the application's database.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class DBBackupTask extends Task
{    
    /**@var string the directory the backups go to.*/
    public $directory;
    
    /**@var string the path to the socket if the dump command should connect trough it.
     * Ignored if the database connection is made through tcp/ip.*/
    public $socket = '';
    
    /**
     * @see CWidget::init()
     */
    public function init()
    {       
        if(!isset($this->directory)) //If there is not backup directory set.
        {
            //Set one by default.
            $this->directory = Yii::getAlias('@app/runtime/dbBackups');
        }
        
        //Translates the name and description.
        $this->description = Yii::t(__CLASS__, 'Backups the database on the server to {dir}', ['dir' => $this->directory]);
        $this->name = Yii::t(__CLASS__, 'Database Backup');
        $this->taskId = 'DatabaseBackupTask';
        $this->interval = self::DAILY;
        
        parent::init(); //Calls the parent.
    }
    
    /** Dumps the whole content of a database.
     * @param string $dsn the connection parameters to the database.
     * @param string $socket the name of the socket to use, leave empty for TCP/IP.
     * @return mixed the database dump of the integer error code if it failed.
     * */
    public static function dumpDb($dsn, $socket = '')
    {
        $dsn = explode(':', $dsn);
        $params = explode(';', $dsn[1]); //Get the connection parameters.
        
        // Splits the parameters into name/values pairs.
        foreach($params as $param)
        {
            $kv = explode('=', $param);
            $params[$kv[0]] = $kv[1];
        }
        
        if(!isset($params['socket']) && $socket) // If a socket is not defined but should be used.
        {
            $params['socket'] = $socket;
        }
        
        switch($dsn[0]) //Switch according to the database type.
        {
            case 'mysql': //Build the mysqldump command with the parameters of the application.
                /*A mysqldump command uses the following syntax:
                 * mysqldump --host=localhost --port=3306 --socket="/tmp/mysql.sock" --protocol=TCP --user=USER --password=PASSWORD DBNAME > PATH_TO_DUMP
                 * */
                $command = self::getDbCommand('mysql', 'mysqldump', $params).' '.$params['dbname'];
                break;
            default:
                throw new \yii\base\Exception('Database type not supported.');
        }
        
        $return = null; //The return of the command.
        $output = ''; //The output of the command.
        exec($command, $output, $return); //Calls the command.
        
        return implode("\n", $output);
        
        //return (int)$return === 0 ? implode("\n", $output): (int)$return;
    } 
    
    /** Creates a generic shell command to access a database to which other arguments
     * can be added.
     * @param string $type the type of the database engine.
     * @param string $command the command to use.
     * @param array $params the connection parameters to pass to the command.
     * @return string the db command to which other arguments can be added.*/
    public static function getDbCommand($type, $command, $params)
    {
        switch($type) //Switch according to the database type.
        {
            case 'mysql':
                $command = 'mysqldump';
                //If connection is done through a socket.
                if((isset($params['host']) && $params['host'] == 'localhost') || isset($params['socket']))
                {
                    if(!isset($params['socket'])) //If no socket is defined.
                    {
                        $socket = '/var/lib/mysql/mysql.sock'; //Use the default socket.
                    }
                    else // Else $params['socket'] is defined.
                    {
                        $socket = $params['socket'];
                    }
                    
                    if(!file_exists($socket)) //Check if the socket exists.
                    {
                        throw new \yii\base\Exception('Socket at '.$socket.' does not exist.');
                    }

                    $command .= ' --socket="'.$socket.'"';
                }
                else //Connection is done through tcp/ip.
                {
                    $command .= isset($params['port']) ? ' --port='.$params['port'] : '';
                    $command .= isset($params['host']) ? ' --host='.$params['host'] : '';
                }
                
                $command .= ' --user='.Yii::$app->db->username;
                $command .= ' --password="'.str_replace('$', '\$', Yii::$app->db->password).'"';
                $command .= ' --max_allowed_packet=100M';
                break;
            default:
                throw new \yii\base\Exception('Database type not supported.');
        }
        
        return $command;
    }
    
    /**
     * Runs the task.
     */
    protected function runTask()
    {          
        if(!file_exists($this->directory)) //If the directory does not exist.
        {
            if(!mkdir($this->directory, 0774, true)) //Create it.
            {
                // Report it as an error.
                $this->errors[] = 'Could not create backup directory at '.$this->directory;
                return;
            }
        }
        
        //Build the filePath accoding to the present date and the number of backups already there.
        $date = new DateTime(); //Right now's datetime object.
        $filePath = $this->directory.DIRECTORY_SEPARATOR.'backup-'.$date->format('dmY');
        $suffix = '';
        for($i = 1 ;file_exists($filePath.$suffix.'.sql'); $i++)
        {
            $suffix = '-'.$i;
        }
        $filePath .= $suffix.'.sql';
        
        // Dump the database and save it into a file.
        @file_put_contents($filePath, $this->dumpDb(Yii::$app->db->dsn));
        
        if(!file_exists($filePath)) //If the backup file was not created.
        {
            $this->errors[] = 'Could not save backup file, please check the permissions for the backup directory.';
            return;
        }
        
        if(!filesize($filePath)) //If the file has a size of 0.
        {
            $this->errors[] = 'Backup file has a size of 0.';
        }
        
        /* Iterates through each file in this directory and delete it if its modification
         * time is older than a month */
        foreach(scandir($this->directory) as $file) 
        {
            $mTime = filemtime($this->directory.DIRECTORY_SEPARATOR.$file);
            $mTime = DateTime::createFromFormat('U', $mTime); // Converts mTime to a DateTime object.
            
            $diff = $date->diff($mTime); // Get the difference between now and that file's modification time.
            
            if($diff->m >= 1) // If the file is older than a month.
            {
                if(!unlink($this->directory.DIRECTORY_SEPARATOR.$file)) // Delete the file.
                {
                    $this->warnings[] = 'Failed to delete file '.$this->directory.DIRECTORY_SEPARATOR.$file;
                }   
            }
        }
    }
}
