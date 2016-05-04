<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

/* @var $className string the new migration class name */

echo "<?php\n";
?>

use \Yii;

/**
 * Represents a database migration of <?= $className ?>.
 */
class <?= $className ?> extends \yiingine\console\DbMigration
{
    /** 
     * Applies the logic to be executed when applying the migration.
     * @return boolean if the migration can be applied. 
     */
    public function up()
    {
        ####################### TABLES #######################
        
        ####################### PERMISSIONS #######################
        
        echo "    > creating permissions ...";
        $time = microtime(true);
        
        // Initialize permissions here.

        echo " done (time: ".sprintf('%.3f', microtime(true) - $time)."s)\n";
        
        return true;
    }
    
    /** 
     * Applies the logic to be executed when reverting a migration.
     * @return boolean if the migration can be reverted.
     */
    public function down()
    {
        echo "<?= $className ?> cannot be reverted.\n";

        return false;
    }
}
