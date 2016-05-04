<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\modules\users\parameters\Requirement;
use \yiingine\modules\users\parameters\Visible;
use \yiingine\modules\users\models\User;
use \yiingine\models\admin\AdminParameters;
use \yiingine\models\MenuItem;
use \yiingine\modules\customFields\models\FormGroup;

/** Represents a database migration of m000000_000001_users.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> */
class m000000_000001_users extends \yiingine\console\DbMigration
{
    /** Applies the logic to be executed when applying the migration.
     * @return boolean if the migration can be applied. */
    public function up()
    {
        $customFieldsModule = $this->module->getModule('profileFields');    
        
        ############################### TABLES #################################
        
        //Create the table that stores users.
        $this->createTable('users', array(
            'id' => 'pk',
            'username' => 'varchar(20) NOT NULL',
            'password' => 'varchar(128) NOT NULL',
            'email' => 'varchar(128) NOT NULL',
            'activation_key' => 'varchar(128) NOT NULL',
            'lastvisit' => 'datetime NOT NULL',
            'superuser' => 'boolean NOT NULL default \'0\'',
            'status' => 'int(1) NOT NULL default \'0\'',
            'dt_crtd' => 'datetime NOT NULL',
            'ts_updt' => 'timestamp NOT NULL default CURRENT_TIMESTAMP',
        )); 
        
        ############################# FORM GROUPS ##############################
        
        echo "    > creating form groups ...";
        $time = microtime(true);
        
        FormGroup::$customFieldsModule = $customFieldsModule;
        
        $group = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Personal', 'fr' => 'Personnel'],
            'level' => 1,
            'position' => 1,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => false
        ]); 
        
        $prefs = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Preferences', 'fr' => 'Préférences'],
            'level' => 1,
            'position' => 2,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => true
        ]);
        
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        ######################## DEFAULT PROFILE FIELDS #######################
        
        echo "    > creating default profile fields ...";
        $time = microtime(true);
        
        $this->addEntry(new \yiingine\modules\customFields\models\VarcharField($customFieldsModule), array(
            'name' => 'last_name',
            'title' => array('en' => 'Last name', 'fr'=> 'Nom de famille'),
            'size' => 50,
            'form_group_id' => $group->id,
            'position' => 1,
            'required' => 1,
            'requirement' => Requirement::REQUIRED_YES_SHOW_REG,
            'configuration' => '',
            'visible' => Visible::VISIBLE_ALL,
            'in_forms' => 1,
        ));
        
        $this->addEntry(new \yiingine\modules\customFields\models\VarcharField($customFieldsModule), array(
            'name' => 'first_name',
            'title' => array('en' => 'First name', 'fr' => 'Prénom'),
            'size' => 50,
            'form_group_id' => $group->id,
            'position' => 2,
            'required' => 1,
            'requirement' => Requirement::REQUIRED_YES_SHOW_REG,
            'configuration' => '',
            'visible' => Visible::VISIBLE_ALL,
            'in_forms' => 1,
        ));
        
        $this->addEntry(new \yiingine\modules\customFields\models\EnumField($customFieldsModule), array(
            'name' => 'admin_display_mode',
            'title' => array('en' => 'Administration panel display mode', 'fr' => 'Mode d\'affichage du panneau d\'administration'),
            'description' => array('en' => 'The administration interface can be displayed in two modes: <ul><li>the <b>standard mode</b>, which displays functions most useful for editing the site, </li><li> the <b>advanced mode</b>, which displays all menus, including those that require an advanced knowledge of the system.</li></ul>',
                'fr' => 'L\'interface d\'administration peut être affichée selon deux modes: <ul><li> le <b>mode standard</b>, qui affiche les fonctions les plus utiles pour éditer le site, </li><li> le <b>mode avancé</b>, qui affiche tous les menus, incluant ceux qui demandent une connaissance approfondie du système.</li></ul>'
            ),
            'configuration' => '["data" => [0 => "STANDARD", 1 => Yii::tA(["en" => "ADVANCED", "fr" => "AVANCÉ"])]]',
            'form_group_id' => $prefs->id,
            'default' => 0,
            'position' => 1,
            'required' => 0,
            'requirement' => Requirement::REQUIRED_NO,
            'visible' => Visible::VISIBLE_ADMIN_INTERFACE,
            'in_forms' => 1,
        ));
        
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        ############################ DEFAULT USERS #############################
        
        echo "    > creating default users ...";
        $time = microtime(true);
        //Create an admin user.
        $admin = $this->addEntry(new User(), array(
            'username' => 'admin',
            'password' => 'admin',
            'verifyPassword' => 'admin',
            'email' => 'admin@example.com',
            'status' => User::STATUS_ACTIVE,
            'superuser' => 1,
            'last_name' => 'Adminlastname',
            'first_name' => 'Adminfirstname',
            'admin_display_mode' => AdminParameters::ADVANCED_DISPLAY_MODE,
        ));
        
        //Create an owner user.
        $owner = $this->addEntry(new User(), array(
            'username' => 'owner',
            'password' => 'owner',
            'verifyPassword' => 'owner',
            'email' => 'owner@example.com',
            'status' => User::STATUS_ACTIVE,
            'superuser' => 1,
            'last_name' => 'Ownerlastname',
            'first_name' => 'Ownerfirstname',
            'admin_display_mode' => AdminParameters::NORMAL_DISPLAY_MODE,
        ));
        
        // If users can register themselves.
        if($this->module->allowRegistration)
        {
            for($i = 0; $i < 3; $i++) // Create three demo user accounts.
            {
                $this->addEntry(new User(), array(
                    'username' => 'user'.$i,
                    'password' => 'user'.$i,
                    'verifyPassword' => 'user'.$i,
                    'email' => 'user'.$i.'@example.com',
                    'status' => User::STATUS_ACTIVE,
                    'superuser' => 0,
                    'last_name' => 'User'.$i.'lastname',
                    'first_name' => 'User'.$i.'firstname',
                    'admin_display_mode' => AdminParameters::NORMAL_DISPLAY_MODE,
                ));
            }
        }
        
        echo " done (time: ".sprintf('%.3f', microtime(true) - $time)."s)\n";
        
        ############################ WEBMASTER USER #############################
        
        echo "    > creating webmaster user ...";
        $time = microtime(true);
        
        // Get the name of the project.
        $array = explode(DIRECTORY_SEPARATOR, realpath(Yii::getAlias('@app')));
        $projectName = array_pop($array);
        
        //Create a webmaster user.
        // NOTE: This user should be deleted for security sensitive sites!
        $webmaster = $this->addEntry(new User(), array(
            'username' => 'webmaster',
            'password' => $projectName.'-webmaster',
            'verifyPassword' => $projectName.'-webmaster',
            'email' => 'webmaster@example.com',
            'status' => User::STATUS_ACTIVE,
            'superuser' => 1,
            'last_name' => 'Webmaster',
            'first_name' => 'Webmaster',
            'admin_display_mode' => AdminParameters::ADVANCED_DISPLAY_MODE,
        ));
        
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        ############################# MODULE MODEL ##############################
        
        if($this->module->enableModuleModel && ($this->module->allowRegistration || $this->module->allowProfileEdition))
        {
            echo "    > creating module model ...";
            $model = $this->module->getModuleModel(); // Create the module model.
    
            $model->setAttributeTranslations('page_content', array(
                'en' => '<p>{{$module}}</p><div style="clear: both;">'.self::getLoremIpsum('en', 1, 0).'</div>',
                'fr' => '<p>{{$module}}</p><div style="clear: both;">'.self::getLoremIpsum('fr', 1, 0).'</div>'
            ));
            $model->setAttributeTranslations('description', array('en' => 'Description for the users module', 'fr' => 'Description pour le module utilisateurs'));
            $model->setAttributeTranslations('keywords', array('en' => 'Keywords for the users module', 'fr' => 'Mots-clés pour le module utilisateurs'));
            $model->save();
    
            // Object association
            $types = array('page', 'insert', /*'GALLERY','IMAGE','VIDEO'*/);
            $mediaModule = Yii::$app->getModule('media');
    
            foreach($mediaModule->mediaClasses as $class)
            {
                foreach($types as $type)
                {
                    if(strpos($class, $type) !== false)
                    {
                        $result = \yiingine\modules\media\models\Medium::find()->where(['type' => $class])->one();
                        $model->link('associated_media', $result);
                    }
                }
            }
            
            echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        }
       
        ################### REGISTRATION AND PROFILE MENUS #####################
        
        echo "    > creating menus ...";
        $time = microtime(true);
        
        // Get last position in mainmenu
        $lastPosition = MenuItem::find()->select('max(position) as max')->where(['parent_id' => 2])->scalar();
        
        // A menu entry that only guests will be able to see.
        $this->addEntry(new MenuItem(), array(
            'name' => array('en' => 'Register', 'fr' => 'Inscription'), 
            'parent_id' => 2, 
            'side' => MenuItem::SITE,
            'rule' => 'Yii::$app->user->isGuest',
            'route' => '/users/register',
            'position' => $lastPosition + 1,
        ));
        // A menu entry that only logged in users will be able to see.
        $this->addEntry(new MenuItem(), array(
            'name' => array('en' => 'Profile', 'fr' => 'Profil'), 
            'parent_id' => 2, 
            'side' => MenuItem::SITE,
            'rule' => '!Yii::$app->user->isGuest',
            'route' => '/users/profile/edit',
            'position' => $lastPosition + 1,
        ));
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        ############################# PERMISSIONS ##############################
        
        echo "    > creating permissions ...";
        $time = microtime(true);
        
        (new \yiingine\modules\media\migrations\ModulePagePermissions($this->module))->up();
        
        $this->createModelPermissions($this->module->id, array('User'));
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole('Administrator'), $admin->id);
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole('Administrator'), $owner->id);
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole('Administrator'), $webmaster->id);
        
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        ####################### MENU ITEMS #######################
        
        $time = microtime(true);
        
        // Find the admin menu.
        if($adminMenu = MenuItem::find()->where(array('name' => 'adminMenu'))->one())
        {
            echo "    > creating users module admin menus ...";
            
            $usersMenu = $this->addEntry(new MenuItem(), array(
                'name' => array('en' => 'Users', 'fr' => 'Utilisateurs'), 
                'parent_id' => $adminMenu->id, 
                'side' => MenuItem::ADMIN,
                'route' => '/users/admin/user',
                'position' => 3,
                'rule' => 'Yii::$app->getModule("'.$this->module->id.'")->checkAccess()'
            ));
                
                $this->addEntry(new MenuItem(), array(
                    'name' => array('en' => 'Users', 'fr' => 'Utilisateurs'), 
                    'parent_id' => $usersMenu->id, 
                    'side' => MenuItem::ADMIN,
                    'route' => '/users/admin/user',
                    'position' => 1,
                    'rule' => 'Yii::$app->user->can("User-view")'
                ));
                
                if($this->module->enableModuleModel)
                {
                    $this->addEntry(new MenuItem(), array(
                        'name' => array('en' => 'Page', 'fr' => 'Page'), 
                        'parent_id' => $usersMenu->id, 
                        'side' => MenuItem::ADMIN,
                        'route' => '/users/admin/module/index',
                        'position' => 2,
                        'rule' => 'Yii::$app->user->can("UsersModule-Page-view")'
                    ));
                }
            
                // Find the users admin menu.
                if($usersMenu = MenuItem::find()->where(array('route' => '/users/admin/user', 'parent_id' => MenuItem::find()->where(array('name' => 'adminMenu'))->one()->id))->one())
                {
                    // Find the rbac admin menu.
                    if($rbacMenu = MenuItem::find()->where(array('name' => 'RBAC'))->one())
                    {
                        $rbacMenu->parent_id = $usersMenu->id;
                        $rbacMenu->save();
                    }
                }
            
                                
            echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
                
            //Create the menu items for the custom fields module.
            (new \yiingine\modules\customFields\migrations\CustomFieldsAdminMenuItems($usersMenu, $this->module->getModule('profileFields'), array('fr' => 'Profil', 'en' => 'Profile')))->up();
        }
    }
    
    /** Applies the logic to be executed when removing a migration.
     * @return boolean if the migration can be removed.*/
    public function down()
    {
        echo "m000000_000001_users does not support migration down.\n";
        return false;
    }
}
