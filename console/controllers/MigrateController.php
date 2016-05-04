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

/**
 * Extends MigrateController to provide migrations for all modules and extensions as once.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class MigrateController extends \yii\console\controllers\MigrateController
{
    /**
     * @var string the default command action.
     */
    public $defaultAction = 'all-up';
    
    /**
     * @var Module the module currently being migrated.
     * */
    protected $currentModule = null;
    
    /**
     * @var inheritdoc
     * */
    public $templateFile = '@yiingine/migrations/template.php';
    
    /**
     * Upgrades the application by applying new migrations from all modules and extensions.
     * @param integer $limit the number of new migrations to be applied. If 0, it means
     * applying all available new migrations.
     * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionAllUp($limit = 0)
    {
        if(Yii::$app->db->schema->getTableSchema('migrations') === null) // If this is the first time the schema is being deployed.
        {
            Yii::$app->params['app.log_active_record_changes'] = false; // Disable logging to speed things up.
        } 
        
        $migrations = [];
        
        // Get yiingine migrations first.
        $this->migrationPath = Yii::getAlias('@yiingine/migrations');
        
        foreach($this->getNewMigrations() as $migration)
        {
            $migrations[] = [$migration, $this->migrationPath];
        }
        
        // Get client site migrations next.
        if(is_dir(Yii::getAlias('@app/migrations')))
        {
            $this->migrationPath = Yii::getAlias('@app/migrations');
            foreach($this->getNewMigrations() as $migration)
            {
                $migrations[] = [$migration, $this->migrationPath];
            }     
        }
        
        // Get module migrations.
        foreach($this->_getModules(Yii::$app) as $module)
        {
            $this->migrationPath = $module->basePath.'/migrations';
            
            // If the module has migrations.
            if(is_dir($this->migrationPath))
            {
                foreach($this->getNewMigrations() as $migration)
                {
                    $migrations[] = [$migration, $this->migrationPath, $module];
                } 
            }
            
            // If the module has extensions.
            if(is_dir($module->basePath.'/extensions'))
            {
                // Get module extension migrations.
                foreach(scandir($module->basePath.'/extensions') as $extension)
                {
                    if(in_array($extension, ['.', '..'])){ continue; }   
                    
                    // If this extension has migrations.
                    if(is_dir($module->basePath.'/extensions/'.$extension.'/migrations'))
                    {
                        $this->migrationPath = $module->basePath.'/extensions/'.$extension.'/migrations';
                        
                        foreach($this->getNewMigrations() as $migration)
                        {
                            $migrations[] = [$migration, $this->migrationPath, $module];
                        } 
                    }
                }
            }
        }
        
        // Remove duplicate migrations from the list.
        $names = [];
        foreach($migrations as $key => $migration)
        {
            if(in_array($migration[0], $names))
            {
                unset($migrations[$key]);
            }
            else
            {
                $names[] = $migration[0];
            } 
        }
        
        if(empty($migrations)) 
        {
            $this->stdout("No new migration found. Your system is up-to-date.\n", Console::FG_GREEN);
            return self::EXIT_CODE_NORMAL;
        }
        
        $total = count($migrations);
        $limit = (int) $limit;
        
        if ($limit > 0) 
        {
            $migrations = array_slice($migrations, 0, $limit);
        }
        
        $n = count($migrations);
        
        if ($n === $total) 
        {
            $this->stdout("Total $n new " . ($n === 1 ? 'migration' : 'migrations') . " to be applied:\n", Console::FG_YELLOW);
        } 
        else 
        {
            $this->stdout("Total $n out of $total new " . ($total === 1 ? 'migration' : 'migrations') . " to be applied:\n", Console::FG_YELLOW);
        }
        
        foreach ($migrations as $migration) 
        {
            $this->stdout("\t".$migration[0]."\n");
        }
        
        $this->stdout("\n");
        if ($this->confirm('Apply the above ' . ($n === 1 ? 'migration' : 'migrations') . "?")) 
        {
            foreach($migrations as $migration) 
            {
                $this->migrationPath = $migration[1];
                $this->currentModule = isset($migration[2]) ? $migration[2]: null; // If a module is being migrated.
                $migration = $migration[0];
                                
                if(!$this->migrateUp($migration)) 
                {
                    $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);
                    return self::EXIT_CODE_ERROR;
                }
            }
            $this->stdout("\nMigrated up successfully.\n", Console::FG_GREEN);
        }
    }
    
    /** 
     * Clears all migrations, effectively erasing the database.
     * */
    public function actionClear()
    {
        if(YII_ENV != 'dev')
        {
            echo 'Clearing only available in development mode.';
            return 2;
        }
        
        if(!$this->confirm("Erase all migrations ?"))
        {
            return 2; // User opted out.
        }
        
        $totalTime = microtime(true);
        
        echo '    > flushing cache ...';
        $time = microtime(true);
        Yii::$app->cache->flush();
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        if(Yii::$app->has('authManager'))
        {
            echo '    > clearing rbac files ...';
            $time = microtime(true);
            Yii::$app->authManager->removeAll();
            echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        }
        
        echo '    > clearing database ...';
        $time = microtime(true);
        foreach(Yii::$app->db->schema->getTableNames() as $name) // Drop tables one by one.
        {
            Yii::$app->db->createCommand()->dropTable($name)->execute();
        }
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        
        echo '    > clearing user files ...';
        $time = microtime(true);
        $path = Yii::getAlias('@webroot/user').DIRECTORY_SEPARATOR;
        
        $exclude = ['.', '..', '.svn'];
        foreach(scandir($path) as $dir)
        {
            if(in_array($dir, $exclude) || is_file($path.$dir))
            {
                continue;
            }
            
            $this->_clearDirectory($path.$dir);
        }
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        echo '    > removing module files ...';
        $time = microtime(true);
        $this->_clearDirectory('@app/modules', $exclude, function($path, $dir){ return is_file($path.$dir.'/'.(ucfirst($dir).'Module.php')); });
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        echo '    > removing module css files ...';
        $time = microtime(true);
        $this->_clearDirectory('@webroot/css/modules', $exclude);
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        echo '    > removing site images ...';
        $time = microtime(true);
        $this->_clearDirectory('@webroot/images/modules', $exclude);
        echo " (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        echo '    > removing assets ...';
        $time = microtime(true);
        $this->_clearDirectory('@webroot/images/modules', $exclude);
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
       $this->stdout("Done (time: ".sprintf('%.3f', microtime(true) - $totalTime)."s)\n", Console::FG_GREEN);
    }
    
    /**
     * Returns the migrations that are not applied.
     * @return array list of new migrations
     */
    protected function getNewMigrations()
    {
        $applied = $this->getMigrationHistory(null);

        $migrations = [];
        $handle = opendir($this->migrationPath);
        while (($file = readdir($handle)) !== false) 
        {
            if ($file === '.' || $file === '..') 
            {
                continue;
            }
            
            $path = $this->migrationPath . DIRECTORY_SEPARATOR . $file;
            
            if(preg_match('/^(m(\d{6}_\d{6})_.*?)\.php$/', $file, $matches) && is_file($path) && !isset($applied[str_replace('.php', '', $file)]))
            {
                $migrations[] = $matches[1];
            }
        }
        closedir($handle);
        sort($migrations);
        
        // return $migrations;
        
        // Check each migration if it applies to the system.
        foreach($migrations as $key => $migration)
        {
            $migration = $this->createMigration($migration);
        
            // Check if the migration applies to this system.
            if($migration instanceof \yiingine\console\DbMigration && !$migration->applies())
            {
                unset($migrations[$key]); // Migration does not apply.
            }
        }
        
        return $migrations;
    }
    
    /**
     * Creates a new migration instance.
     * @param string $class the migration class name
     * @return \yii\db\MigrationInterface the migration instance
     */
    protected function createMigration($class)
    {
        $file = $this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php';
        require_once($file);

        $migration = new $class();
        
        if($migration instanceof \yiingine\console\DbMigration)
        {
            $migration->module = $this->currentModule;
        }
        
        return $migration;
    }
    
    /** Removes the content of a directory.
     * @param string $path the path or alias to the directory.
     * @param array $exclude file names to exclude.
     * @param function $filter anonymous function that returns true if its ($path, $dir) argument
     * should be skipped.
     * */
    private function _clearDirectory($path, $exclude = [], $filter = null)
    {
        $exclude = array_merge(['.', '..'], $exclude);
        
        $path = Yii::getAlias($path).DIRECTORY_SEPARATOR;
        
        if(!is_dir($path)) // Exit if the path does not exist.
        {
            return;
        }
        
        foreach(scandir($path) as $dir)
        {
            if(in_array($dir, $exclude) || ($filter !== null && $filter($path, $dir)))
            { 
                continue;
            }

            if(is_dir($path.$dir))
            { 
                FileHelper::removeDirectory($path.$dir);
                // exec('rm -Rf '.$path.$dir);
            }
            else
            { 
                unlink($path.$dir);
            }
        }
    }
    
    /**
     * Retrieves module hierarchy as a list.
     * @param Module $module the module to get children from.
     * @return array a flat list of all the modules in use.
     * */
    private function _getModules($module)
    {
        $modules = [];
    
        // For each child modules of $module.
        foreach($module->modules as $name => $config)
        {
            $childModule = $module->getModule($name); //Instantiate the child module.
            //            
            // Get all modules for this child.
            /* The merge must occur in this order for child modules to get initialized
             * before their parent.*/
            $modules = array_merge($modules, $this->_getModules($childModule));
            
            $modules[$name] = $childModule;
        }
        
        return $modules;
    }
}
