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
use \yiingine\tasks\DBBackupTask;
use \yii\db\Connection;

/**
 * This command is used to manipulate databases.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class DatabaseController extends \yii\console\Controller
{    
    /** An action for copying a database into another.
     * @param string $from the name of the database to get the data from.
     * @param string $to the name of the database to load the data into.*/
    public function actionCopy($from, $to)
    {
        if($from === true || $from == '' || !isset($from)) // If the from database was not provided.
        {
            echo 'Origin database name missing.'."\n";
            exit(1);
        }
        if($to === true || $to == '' || !isset($to)) // If the from database was not provided.
        {
            echo 'Destination database name missing.'."\n";
            exit(1);
        }
        
        if($to == $from) // If the two databases are the same.
        {
            echo 'Origin and destination databases cannot be the same.'."\n";
            exit(1);
        }
        
        // Get the configuration for both databases.
        $dbLocation = $from;
        $fromConfig = require(Yii::getAlias('@app/config/console.php'));
        $dbLocation = $to;
        $toConfig = require(Yii::getAlias('@app/config/console.php'));
        
        // If the origin database configuration generated no connection parameters. 
        if(!isset($fromConfig['components']['db']) || !($fromConfig['components']['db']))
        {
            echo $from.' is not a valid database for this application.'."\n";
            return 1;
        }
        // If the destination database configuration generated no connection parameters. 
        if(!isset($toConfig['components']['db']))
        {
            echo $to.' is not a valid database for this application.'."\n";
            return 1;
        }
        
        if(!$this->confirm("Are you sure you want to copy $from to $to ?"))
        {
            return 2; //User opted out.
        }
        
        echo '*** copying database '.$from.' to database '.$to."\n";
        $totalTime = microtime(true);
        
        $fromConfig = $fromConfig['components']['db'];
        $toConfig = $toConfig['components']['db'];
        
        // Establish the database connections to test their validity.
        $fromConnection = new Connection($fromConfig);
        $toConnection = new Connection($toConfig);
        
        echo '    > saving destination database ...';
        $time = microtime(true);
        
        // Creates the destination directory for the backup if it does not exist.
        if(!file_exists(Yii::getAlias('@app/runtime/dbCopyBackups')))
        {
            mkdir(Yii::getAlias('@app/runtime/dbCopyBackups'));
        }
        $fileName = Yii::getAlias('@app/runtime/dbCopyBackups/'.uniqid().'-'.$to.'.sql');
        file_put_contents($fileName, DBBackupTask::dumpDb($toConnection->dsn));
        echo " done at $fileName (time: ".sprintf('%.3f', microtime(true) - $time)."s)\n";
        
        echo '    > dumping origin database ...';
        $time = microtime(true);
        // If the dump is not a string, it failed.
        if(!is_string($fromDump = DBBackupTask::dumpDb($fromConnection->dsn)))
        {
            echo " failed with error code $fromDump (time: ".sprintf('%.3f', microtime(true) - $time)."s)\n";
            return 1;
        }
        echo " done (time: ".sprintf('%.3f', microtime(true) - $time)."s)\n";
        
        echo '    > erasing destination database ...';
        $time = microtime(true);
        foreach($toConnection->schema->getTableNames() as $name) // Drop tables one by one.
        {
            $toConnection->createCommand('DROP TABLE `'.$name.'`')->execute();
        }
        $toConnection->schema->refresh();
        echo " done (time: ".sprintf('%.3f', microtime(true) - $time)."s)\n";
        
        echo '    > copying data to destination database ...';
        $time = microtime(true);
        $toConnection->createCommand($fromDump)->execute();
        echo " done (time: ".sprintf('%.3f', microtime(true) - $time)."s)\n";
        
        echo '    > flushing cache ...';
        $time = microtime(true);
        Yii::$app->cache->flush();
        //Clear cache manually.
        exec('rm -Rf '.Yii::getAlias('@app/runtime/cache').DIRECTORY_SEPARATOR.'*');
        echo " done (time: ".sprintf('%.3f', microtime(true) - $time)."s)\n";
        
        echo "*** copied databases (time: ".sprintf('%.3f', microtime(true) - $totalTime)."s)\n";
    }
    
    /** An action for dumping the content of a database.
     * @param string $from the name of the database to dump.*/
    public function actionDump($from)
    {
        if($from === true || $from == '') // If the database was not provided.
        {
            echo 'Database name missing.'."\n";
            return 1;
        }
        
        // Get the configuration of the database.
        $dbLocation = $from;
        $config = require(Yii::getAlias('@app/config/console.php'));
        
        // If the origin database configuration generated no connection parameters. 
        if(!isset($config['components']['db']) || !($config['components']['db']))
        {
            echo $from.' is not a valid database for this application.'."\n";
            return 1;
        }
        
        // If this database configuration generated no connection parameters. 
        if(!isset($config['components']['db']))
        {
            echo $dbLocation.' is not a valid database for this application.'."\n";
            return 1;
        }
        
        // Establish the database connection to test its validity.
        $connection = new Connection($config['components']['db']);
        
        // If the dump is not a string, it failed.
        if(!is_string($output = DBBackupTask::dumpDb($connection->dsn)))
        {
            echo "Dump failed with error code $output\n";
            return 1;
        }
        
        echo $output."\n";
    }
    
    /** Displays the help */
    public function getHelp()
    {
        return <<<EOD
USAGE
  yiic database action --from=origin [--to=destination]
    
DESCRIPTION
  This command provides tools to dump and copy databases defined in
  an application's configuration. Before undertaking a destructive operation,
  the tool backs up the databases in the runtime folder.
    
EXAMPLES
 * yiic database dump --from=remote
   Dumps the content of the remote database to stdout.
           '
 * yiic database dump --from=remote > dump.sql
   Dumps the content of the remote database to dump.sql.
    
 * yiic database copy -from=remote --to=local
   Saves the content of the local database, dumps the content
   of the remote database, erases the local database and loads
   the dump in it.
EOD;
    }
}
