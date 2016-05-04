<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\migrations;

use \Yii;
use \yiingine\models\MenuItem;

/** Adds admin menu items for an instance of the custom fields module.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> */
class CustomFieldsAdminMenuItems extends \yiingine\console\DbMigration
{
    /** @var MenuItem the root menu item to which the custom fields module's menus should be attached.*/
    public $rootMenu;
    
    /** @var CustomFieldsModule an instance of the module to which the menus will refer. */
    public $customFieldsModule;
    
    /** @var array the translation array of the name of the model which is getting its fields customized.*/
    public $name;
    
    /** Constructor method.
     * @param MenuItem $rootMenu the root menu item to which the custom fields module's menus should be attached.
     * @param CustomFieldsModule $customFieldsModule an instance of the module to which the menus will refer.
     * @param array the translation array of the name of the model which is getting its fields customized.
     * */
    public function __construct($rootMenu, $customFieldsModule, $name)
    {
        $this->rootMenu = $rootMenu;
        $this->customFieldsModule = $customFieldsModule;
        $this->name = $name;
    }
    
    /** Applies the logic to be executed when applying the migration.
     * @return boolean if the migration can be applied. */
    public function up()
    {
        ####################### MENU ITEMS #######################
        
        $moduleName = $this->customFieldsModule->id;
        $parentModuleName = $this->customFieldsModule->module->id;
        
        echo "    > creating $parentModuleName custom fields module admin menus ...";
        $time = microtime(true);
        
        $customFieldsMenu = $this->addEntry(new MenuItem(), array(
            'name' => array('en' => $this->name['en'].' Fields', 'fr' => 'Champs de '.$this->name['fr']), 
            'parent_id' => $this->rootMenu->id, 
            'side' => MenuItem::ADMIN,
            'route' => '/'.$parentModuleName.'/'.$moduleName.'/admin/custom-field',
            'position' => 99,
            'css_class' => 'advanced',
            'rule' => 'Yii::$app->controller->adminDisplayMode === AdminParameters::ADVANCED_DISPLAY_MODE && Yii::$app->getModule("'.$parentModuleName.'")->getModule("'.$moduleName.'")->checkAccess()',
        ));
        
        $this->addEntry(new MenuItem(), array(
            'name' => array('en' => 'Custom Fields', 'fr' => 'Champs Personalisés'), 
            'parent_id' => $customFieldsMenu->id, 
            'side' => MenuItem::ADMIN,
            'route' => '/'.$parentModuleName.'/'.$moduleName.'/admin/custom-field',
            'position' => 1,
            'css_class' => 'advanced',
            'rule' => 'Yii::$app->controller->adminDisplayMode === AdminParameters::ADVANCED_DISPLAY_MODE && Yii::$app->user->can("CustomField-'.$parentModuleName.'-view")',
        ));
        
        $this->addEntry(new MenuItem(), array(
            'name' => array('en' => 'Form Groups', 'fr' => 'Groupes de Formulaire'), 
            'parent_id' => $customFieldsMenu->id, 
            'side' => MenuItem::ADMIN,
            'route' => '/'.$parentModuleName.'/'.$moduleName.'/admin/form-group',
            'position' => 2,
            'css_class' => 'advanced',
            'rule' => 'Yii::$app->controller->adminDisplayMode === AdminParameters::ADVANCED_DISPLAY_MODE && Yii::$app->user->can("FormGroup-view")',
        ));
                
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
    }
    
    /** Applies the logic to be executed when removing a migration.
     * @return boolean if the migration can be removed.*/
    public function down()
    {
        /* No migration down, simply delete the whole menu tree for the module from the root. 
         * and the menus for the custom field module will be deleted as well.*/
    }
}
