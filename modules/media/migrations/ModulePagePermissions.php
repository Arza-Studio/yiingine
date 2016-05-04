<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\migrations;

use \Yii;

/** Creates the permissions for management of the module page.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> */
class ModulePagePermissions extends \yiingine\console\DbMigration
{    
    /** @var CMediaModule an instance of the module to which the menu will be created. */
    public $module;
    
    /** Constructor method.
     * @param CMediaModule $module an instance of the module to which the menu will be created.
     * */
    public function __construct($module)
    {
        $this->module = $module;
    }
    
    /** Applies the logic to be executed when applying the migration.
     * @return boolean if the migration can be applied. */
    public function up()
    {
        $label = 'Module-Page';
        
        $authManager = Yii::$app->authManager;
        
        // Makes sure the task for manipulating all module pages exists.
        if(!($mainTask = $authManager->getItem('Module-Page-manage')))
        {
            $mainTask = $authManager->createPermission($label.'-manage', Yii::tA(['en' => 'Manage '.$label, 'fr' => 'Gestion de '.$label]));
            $authManager->add($mainTask);
            
            $view = $authManager->createPermission($label.'-view', Yii::tA(['en' => 'View '.$label, 'fr' => 'Consultation de '.$label]));
            $authManager->add($view);
            $authManager->addChild($mainTask, $view);
            
            $update = $authManager->createPermission($label.'-update', Yii::tA(['en' => 'Update '.$label, 'fr' => 'Modification de '.$label]));
            $authManager->add($update);
            $authManager->addChild($mainTask, $update);
        }
        
        $label = ucfirst($this->module->id).'Module-Page';
        
        // If manage role for this model has been created already.
        if($authManager->getItem($label.'-manage'))
        {
            return true; // Do not overwrite already existing permissions.
        }
        
        $task = $authManager->createPermission($label.'-manage', Yii::tA(['en' => 'Manage '.$label, 'fr' => 'Gestion de '.$label]));
        $authManager->add($task);
        
        $view = $authManager->createPermission($label.'-view', Yii::tA(['en' => 'View '.$label, 'fr' => 'Consultation de '.$label]));
        $authManager->add($view);
        $authManager->addChild($task, $view);
        
        $update = $authManager->createPermission($label.'-update', Yii::tA(['en' => 'Update '.$label, 'fr' => 'Modification de '.$label]));
        $authManager->add($update);
        $authManager->addChild($task, $update);
    }
    
    /** Applies the logic to be executed when removing a migration.
     * @return boolean if the migration can be removed.*/
    public function down()
    {
        $name = ucfirst($this->module->id).'Module-Page-';
        
        Yii::$app->authManager->remove($name.'view');
        Yii::$app->authManager->remove($name.'update');
        Yii::$app->authManager->remove($name.'manage');
    }
}
