<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

/** Represents a database migration of m000000_000001_customFields.
 * This migration only cares for the database structures that are common
 * between all instance of this module. Whathever is specific to an instance
 * should be managed in the parent module.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> */
class m000000_000001_customFields extends \yiingine\console\DbMigration
{
    /** Applies the logic to be executed when applying the migration.
     * @return boolean if the migration can be applied. */
    public function up()
    {
        ####################### TABLES #######################
        
        //NOTE: the form group table is shared between all custom fields modules.
        
        $this->createTable('form_groups', array(
            'id' => 'pk',
            'name' => 'string NOT NULL',
            'collapsed' => 'boolean NOT NULL default \'0\'',
            'position' => 'integer NOT NULL default \'0\'',
            'level' => 'integer NOT NULL default \'1\'',
            'parent_id' => 'integer NOT NULL default \'0\'',
            'owner' => 'string NOT NULL',
            'dt_crtd' => 'datetime NOT NULL',
            'ts_updt' => 'timestamp NOT NULL default CURRENT_TIMESTAMP',
        ));
    }
    
    /** Applies the logic to be executed when removing a migration.
     * @return boolean if the migration can be removed.*/
    public function down()
    {
        return false; // Not supported.
    }
}
