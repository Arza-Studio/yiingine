<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\migrations;

use \Yii;

/** Represents a database migration of an instance of the custom fields module
 * This migration is meant to called (by extending its class) from the module
 * that uses it.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> */
abstract class _m000000_000001_fields extends \yiingine\console\DbMigration
{
    /** @var string the name of the custom fields module instance.*/
    protected $customFieldsModule;
    
    /** Applies the logic to be executed when applying the migration.
     * @return boolean if the migration can be applied. */
    public function up()
    {
        if(!($customFieldsModule = $this->module->getModule($this->customFieldsModule)))
        {
            echo "    > error: the module $this->customFieldsModule does not exist in module {$this->module->id}.\n";
            return false;
        }
        
        ####################### TABLES #######################
        
        //Create the table that stores custom field configurations.
        $this->createTable($customFieldsModule->tableName, [
            'id' => 'pk',
            'name' => 'varchar(50) NOT NULL',
            'title' => 'string NOT NULL',
            'description' => 'text NOT NULL',
            'form_group_id' => 'integer NOT NULL default \'0\'',
            'type' => 'varchar(255) NOT NULL',
            'size' => 'int(3) NOT NULL default \'0\'',
            'min_size' => 'int(3) NOT NULL default \'0\'',
            'required' => 'boolean NOT NULL default \'0\'',
            'configuration' => 'text NOT NULL',
            'validator' => 'string NOT NULL default \'\'',
            'default' => 'text NOT NULL',
            'in_forms' => 'boolean NOT NULL default \'1\'',
            'translatable' => 'boolean NOT NULL default \'0\'',
            'position' => 'integer NOT NULL default \'0\'',
            'protected' => 'boolean NOT NULL default \'0\'',
            'dt_crtd' => 'datetime NOT NULL',
            'ts_updt' => 'timestamp NOT NULL default CURRENT_TIMESTAMP',
        ]);
        
        ####################### FIELD PARAMETERS #######################
        
        echo "    > creating field parameters ...";
        $time = microtime(true);        

        foreach($customFieldsModule->getFieldParameters() as $param)
        {
            Yii::$app->db->createCommand()->addColumn($customFieldsModule->tableName, $param->name, $param->getSql())->execute();
        }
        
        Yii::$app->db->schema->refresh(); // Schema changed so we must refresh it.
        
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        ####################### PERMISSIONS #######################
        
        echo "    > creating permissions ...";
        $time = microtime(true);
        $this->createModelPermissions($customFieldsModule->id, ['CustomField-'.$this->module->id]);
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
    }
    
    /** Applies the logic to be executed when removing a migration.
     * @return boolean if the migration can be removed.*/
    public function down()
    {
        $customFieldsModule = $this->module->getModule($this->customFieldsModule);
         
        //Delete all form groups that belong to this module.
        echo "    > deleting form groups ...";
        $time = microtime(true);
        FormGroup::$customFieldsModule = $customFieldsModule;
        FormGroup::deleteAllWithEvents(['owner' => $customFieldsModule->tableName]);
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
         
        $this->dropTable($customFieldsModule->tableName);
        
        echo "    > deleting permissions ...";
        $time = microtime(true);
        $this->deleteModelPermissions($customFieldsModule->id, ['CustomField-'.$this->module->id]);
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
    }
}
