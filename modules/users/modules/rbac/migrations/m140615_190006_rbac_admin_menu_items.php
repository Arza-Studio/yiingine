<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\models\MenuItem;

/** Represents a database migration of m140615_190006_rbac_admin_menu_items.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> */
class m140615_190006_rbac_admin_menu_items extends \yiingine\console\DbMigration
{
    /** Applies the logic to be executed when applying the migration.
     * @return boolean if the migration can be applied. */
    public function up()
    {
        ####################### MENU ITEMS #######################
        
        echo "    > creating rbac module admin menus ...";
        $time = microtime(true);
        
        // Find the admin menu.
        if(!$adminMenu = MenuItem::find()->where(array('name' => 'adminMenu'))->one())
        {
            // The menu was not found, nothing else to do.
            echo " done (time: ".sprintf('%.3f', microtime(true) - $time)."s)\n";
            return;
        }
        
        $rbacMenu = $this->addEntry(new MenuItem(), array(
            'name' => array('en' => 'RBAC', 'fr' => 'RBAC'), 
            'parent_id' => $adminMenu->id, 
            'side' => MenuItem::ADMIN,
            'route' => '/users/rbac/admin/role/index',
            'position' => 5,
            'css_class' => 'advanced',
            'rule' => 'Yii::$app->user->can("Administrator") && Yii::$app->controller->adminDisplayMode === AdminParameters::ADVANCED_DISPLAY_MODE'
        ));
            
            $this->addEntry(new MenuItem(), array(
                'name' => array('en' => 'Roles', 'fr' => 'Rôles'), 
                'parent_id' => $rbacMenu->id, 
                'side' => MenuItem::ADMIN,
                'route' => '/users/rbac/admin/role/index',
                'position' => 1,
                'rule' => 'Yii::$app->user->can("Administrator")'
            ));
            
            $this->addEntry(new MenuItem(), array(
                'name' => array('en' => 'Permissions', 'fr' => 'Permissions'), 
                'parent_id' => $rbacMenu->id, 
                'side' => MenuItem::ADMIN,
                'route' => '/users/rbac/admin/permission/index',
                'position' => 2,
                'rule' => 'Yii::$app->user->can("Administrator")'
            ));
            
            $this->addEntry(new MenuItem(), array(
                'name' => array('en' => 'Assignments', 'fr' => 'Assignations'), 
                'parent_id' => $rbacMenu->id, 
                'side' => MenuItem::ADMIN,
                'route' => '/users/rbac/admin/assignment/index',
                'position' => 3,
                'rule' => 'Yii::$app->user->can("Administrator")'
            ));
        
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
    }
    
    /** Applies the logic to be executed when removing a migration.
     * @return boolean if the migration can be removed.*/
    public function down()
    {
        echo "    > deleting rbac module admin menus ...";
        $time = microtime(true);
        
        // Find the rbac admin menu.
        if(!$rbacMenu = MenuItem::find()->where(array('name' => 'RBAC'))->one())
        {
            // The menu was not found, nothing else to do.
            echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
            return;
        }
        
        $rbacMenu->deleteMenuTree();
        
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
    }
}
