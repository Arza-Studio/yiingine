<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\models\MenuItem;
use \yiingine\models\ConfigEntry;

/** Represents a database migration of m000000_000001_Yiingine.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> */
class m000000_000001_Yiingine extends \yiingine\console\DbMigration 
{
    /** Applies the logic to be executed when applying the migration.
     * @return boolean if the migration can be applied. */
    public function up() 
    {        
        $migrationTable = Yii::$app->controller->migrationTable;
        
        // If this migration is an update of the Engine 1.x. 
        if((new \yii\db\Query())->select(['version'])->from($migrationTable)->where(['version' => 'm000000_000001_Engine'])->one())
        {
            return true; // Nothing to do.
        }
        
        // Using the version column as a primary key creates conflicts.
        $this->dropPrimaryKey('version', $migrationTable);
        $this->addColumn($migrationTable, 'id', 'pk FIRST');
        
        ####################### TABLES #######################
        // Create the table that stores configuration entries.
        $this->createTable('config', array(
            'id' => 'pk',
            'name' => 'string NOT NULL',
            'value' => 'text NOT NULL',
            'translatable' => 'boolean NOT NULL default "0"',
            'dt_crtd' => 'datetime NOT NULL',
            'ts_updt' => 'timestamp NOT NULL default CURRENT_TIMESTAMP',
        ));

        // Create the table that stores menu items.
        $this->createTable('menus', array(
            'id' => 'pk',
            'parent_id' => 'integer NOT NULL default "0"',
            'name' => 'string NOT NULL',
            'position' => 'integer NOT NULL default "1"',
            'displayed' => 'boolean NOT NULL default "1"',
            'enabled' => 'boolean NOT NULL default "1"',
            'route' => 'string NOT NULL default ""',
            'parameters' => 'string NOT NULL default ""',
            'arguments' => 'string NOT NULL default ""',
            'fragment' => 'string NOT NULL default ""',
            'side' => 'integer NOT NULL default "2"',
            'rule' => 'text NOT NULL',
            'css_class' => 'string NOT NULL default ""',
            'target' => 'string NOT NULL default ""',
            'opts' => 'string NOT NULL default ""',
            'model_id' => 'integer NOT NULL default "0"',
            'model_class' => 'string NOT NULL default ""',
            'dt_crtd' => 'datetime NOT NULL',
            'ts_updt' => 'timestamp NOT NULL default CURRENT_TIMESTAMP',
        ));

        // Create the table that stores tasks reports.
        $this->createTable('task_reports', array(
            'id' => 'pk',
            'task_id' => 'string NOT NULL',
            'status' => 'integer NOT NULL',
            'report' => 'text NOT NULL',
            'execution_date' => 'datetime NOT NULL',
        ));

        // Create the table that stores url rewriting rules.
        $this->createTable('url_rewriting_rules', array(
            'id' => 'pk',
            'position' => 'integer NOT NULL default "1"',
            'enabled' => 'boolean NOT NULL default "1"',
            'pattern' => 'string NOT NULL default ""',
            'defaults' => 'string NOT NULL default ""',
            'encode_params' => 'boolean NOT NULL default "1"',
            'host' => 'string NOT NULL default ""',
            'mode' => 'integer NOT NULL default "0"',
            'route' => 'string NOT NULL default ""',
            'suffix' => 'VARCHAR(31) NOT NULL default ""',
            'verb' => 'VARCHAR(31) NOT NULL default ""',
            'languages' => 'string NOT NULL default ""',
            'system_generated' => 'boolean NOT NULL default "0"',
            'dt_crtd' => 'datetime NOT NULL',
            'ts_updt' => 'timestamp NOT NULL default CURRENT_TIMESTAMP',
        ));

        // Create the table that stores log entries.
        $this->createTable('active_record_changelog', array(
            'id' => 'pk',
            'action' => 'string NOT NULL',
            'model' => 'string NOT NULL',
            'model_id' => 'string NOT NULL',
            'model_table' => 'string NOT NULL',
            'model_title' => 'string NOT NULL',
            'model_admin_url' => 'string NOT NULL',
            'user_name' => 'string NOT NULL',
            'user_id' => 'integer NOT NULL',
            'attribute' => 'string NOT NULL',
            'previous_attribute_value' => 'text NOT NULL',
            'new_attribute_value' => 'text NOT NULL',
            'datetime' => ' datetime NOT NULL',
        ));

        ####################### TEMP DIRECTORY #######################

        if (!file_exists(Yii::getAlias('@webroot/user/temp'))) 
        {
            \yii\helpers\FileHelper::createDirectory(Yii::getAlias('@webroot/user/temp'));
            \yii\helpers\FileHelper::createDirectory(Yii::getAlias('@webroot/user/temp/assets'));
        }

        if (!file_exists(Yii::getAlias('@webroot/user/temp/assets')))
        {
            \yii\helpers\FileHelper::createDirectory(Yii::getAlias('@webroot/user/temp/assets'));
        }
        
        ####################### CONFIGURATION ENTRIES #######################
        
        echo "    > adding default configuration entries ...";
        $time = microtime(true);
        $assets = Yii::getAlias('@yiingine/migrations/_'.get_class($this).'_assets');
        //Site
        $this->addEntry(new ConfigEntry(), array('name' => 'app.main_domain', 'value' => 'test.arza-studio.com', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.alternate_domains', 'value' => 'test.arza-studio.fr, test.arza-studio.org', 'translatable' => 0));
        // Metas
        $this->addEntry(new ConfigEntry(), array('name' => 'yiingine.SocialMetas.meta_robots', 'value' => 'all', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'yiingine.SocialMetas.meta_copyright', 'value' => array( 'en' => 'Copyright owner.', 'fr' => 'Détenteur des droits d\'auteur.'), 'translatable' => 1));
        $this->addEntry(new ConfigEntry(), array('name' => 'yiingine.SocialMetas.meta_viewport', 'value' => 'width=device-width, initial-scale=1', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.google_analytics_key', 'value' => 0, 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'yiingine.SocialMetas.meta_google', 'value' => 'notranslate', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.bing_app_id', 'value' => 0, 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.facebook_admin_id', 'value' => 0, 'translatable' => 0));
        // Browsers
        $this->addEntry(new ConfigEntry(), array('name' => 'app.incompatible_browsers', 'value' => '"windows" => array("ie4", "ie5", "ie6"), "apple" => array("ie")', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.require_javascript', 'value' => '1', 'translatable' => 0));
        // Languages
        $this->addEntry(new ConfigEntry(), array('name' => 'app.use_language_prefs', 'value' => 1, 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.available_languages', 'value' => implode(',', Yii::$app->params['app.available_languages']), 'translatable' => 0));
        // Session
        $this->addEntry(new ConfigEntry(), array('name' => 'app.session_timeout', 'value' => 1800, 'translatable' => 0));
        // Website Info
        $this->addEntry(new ConfigEntry(), array('name' => 'app.name', 'value' => array('en' => 'Application Name', 'fr' => 'Nom de l\'Application'), 'translatable' => 1));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.catchphrase', 'value' => array('en' => 'Catchprase with keyword1 and keyword2', 'fr' => 'Accroche avec motclé1 et motclé2'), 'translatable' => 1));
        // Graphic elements
        $this->addEntry(new ConfigEntry(), array('name' => 'app.main_logo', 'value' => 'main-logo.svg', 'translatable' => 0));
        copy($assets . '/main-logo.svg', Yii::getAlias('@webroot/user/assets') . '/main-logo.svg');
        $this->addEntry(new ConfigEntry(), array('name' => 'app.main_logo_reduced', 'value' => 'main-logo-reduced.svg', 'translatable' => 0));
        copy($assets . '/main-logo-reduced.svg', Yii::getAlias('@webroot/user/assets') . '/main-logo-reduced.svg');
        $this->addEntry(new ConfigEntry(), array('name' => 'app.favicon', 'value' => 'favicon.png', 'translatable' => 0));
        copy($assets . '/favicon.png', Yii::getAlias('@webroot/user/assets') . '/favicon.png');
        $this->addEntry(new ConfigEntry(), array('name' => 'app.apple_touch_icon', 'value' => 'apple-touch-icon.png', 'translatable' => 0));
        copy($assets . '/apple-touch-icon.png', Yii::getAlias('@webroot/user/assets') . '/apple-touch-icon.png');
        $this->addEntry(new ConfigEntry(), array('name' => 'app.default_background', 'value' => 'test-default-image.jpg', 'translatable' => 0));
        copy($assets . '/test-default-image.jpg', Yii::getAlias('@webroot/user/assets') . '/test-default-image.jpg');
        // Brand/Company/Corporate info
        $this->addEntry(new ConfigEntry(), array('name' => 'app.brand_name', 'value' => array('en' => 'Brand name', 'fr' => 'Nom de marque'), 'translatable' => 1));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.brand_logo', 'value' => 'brand-name.jpg', 'translatable' => 0));
        copy($assets . '/brand-name.jpg', Yii::getAlias('@webroot/user/assets') . '/brand-name.jpg');
        // Owner info
        $this->addEntry(new ConfigEntry(), array('name' => 'app.owner_name', 'value' => 'Name', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.owner_last_name', 'value' => 'LastName', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.owner_street', 'value' => '00, Street Name', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.owner_postal_code', 'value' => '00000', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.owner_city', 'value' => 'City', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.owner_country', 'value' => array('en' => 'Country', 'fr' => 'Pays'), 'translatable' => 1));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.owner_telephone1', 'value' => '+00000000000', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.owner_telephone2', 'value' => '+00000000000', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.owner_fax', 'value' => '+00000000000', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.owner_email1', 'value' => 'sample1@sample.com', 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'app.owner_email2', 'value' => 'sample2@sample.com', 'translatable' => 0));
        // System Email
        $this->addEntry(new ConfigEntry(), array('name' => 'app.system_email', 'value' => 'system@sample.com', 'translatable' => 0));
        // Admin Preferences
        $this->addEntry(new ConfigEntry(), array('name' => 'yiingine.admin.default_page_size', 'value' => 100, 'translatable' => 0));
        // Announcement. Displayed so the design of the site can factor in its presence.
        $this->addEntry(new ConfigEntry(), array('name' => 'app.announcement', 'value' => array('en' => 'Welcome to the template! The information bar displays short messages in the foreground.', 'fr' => 'Bienvenue sur le template ! Cette barre d\'information permet d\'afficher des messages ponctuels au premier plan.'), 'translatable' => 1));
        // Social Links
        $socialLinks = [
            'https://plus.google.com', // Google +
            'https://www.facebook.com', // Facebook
            'https://twitter.com', // Twitter
            'http://www.linkedin.com', // Linked In
            'http://www.youtube.com', // YouTube
            'http://www.sample.com', // Additional networks
        ];
        $this->addEntry(new ConfigEntry(), array('name' => 'app.social_links', 'value' => implode(',', $socialLinks), 'translatable' => 0));
        // Problem reporting
        $this->addEntry(new ConfigEntry(), array('name' => 'yiingine.error_reporting.enabled', 'value' => 1, 'translatable' => 0));
        $this->addEntry(new ConfigEntry(), array('name' => 'yiingine.error_reporting.email', 'value' => 'error@sample.com', 'translatable' => 0));
        
        // Task Control
        $this->addEntry(new ConfigEntry(), array('name' => 'yiingine.DatabaseBackupTask.enabled', 'value' => '1', 'translatable' => 0));
        
        echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";
        
        ####################### MENUS #######################

        echo "    > creating main menus ...";
        $time = microtime(true);
        //Adds a main, header and footer menus.
        $mainMenu = $this->addEntry(new MenuItem(), array('name' => 'mainMenu', 'position' => 0));
        $footerMenu = $this->addEntry(new MenuItem(), array('name' => 'footerMenu', 'position' => 0));
        $headerMenu = $this->addEntry(new MenuItem(), array('name' => 'headerMenu', 'position' => 0));

        echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";
        
        echo "    > creating main admin menus ...";
        $time = microtime(true);
        
        // Root menu for the admin site.
        $adminMenu = $this->addEntry(new MenuItem(), array(
            'name' => 'adminMenu', 
            'parent_id' => 0, 
            'side' => MenuItem::ADMIN,
            'position' => 0
        ));
        
            // Modules menu
            $this->addEntry(new MenuItem(), array(
                'name' => array('en' => 'Dashboard', 'fr' => 'Tableau de bord'), 
                'parent_id' => $adminMenu->id, 
                'side' => MenuItem::ADMIN,
                'route' => '/admin',
                'position' => 1,
                // The modules listed belowe are all handled in a special way so they are not part of the "Modules" menu.
                'rule' => 'array_diff(array_keys(Yii::$app->getModules()), ["media", "users", "debug", "gii"])'
            ));
        
            // Content menu : cf. media module migrations
        
            // Modules menu
            $this->addEntry(new MenuItem(), array(
                'name' => array('en' => 'Modules', 'fr' => 'Modules'), 
                'parent_id' => $adminMenu->id, 
                'side' => MenuItem::ADMIN,
                'route' => '#',//'/'.$modules[2].'/admin',
                'position' => 3,
                // The Modules menu is only displayed when it has children.
                'rule' => '$model->getMenuItems()->count()'
            ));
            
            // User menu : cf. user module migrations
        
            // Config menu
            $configMenu = $this->addEntry(new MenuItem(), array(
                'name' => array('en' => 'Configuration', 'fr' => 'Configuration'), 
                'parent_id' => $adminMenu->id, 
                'side' => MenuItem::ADMIN,
                'route' => '/admin/config',
                'position' => 5,
                'css_class' => 'advanced',
                'rule' => 'Yii::$app->controller->adminDisplayMode === AdminParameters::ADVANCED_DISPLAY_MODE' .
                        ' && (' .
                        '    Yii::$app->user->can("ConfigEntry-view") ||' .
                        '    Yii::$app->user->can("MenuItem-view") ||' .
                        '    Yii::$app->user->can("UrlRewritingRule-view") ||' .
                        '    Yii::$app->user->can("ActiveRecordLogEntry-view")' .
                        ')',
            ));

                $site = $this->addEntry(new MenuItem(), array(
                    'name' => array('en' => 'Site', 'fr' => 'Site'), 
                    'parent_id' => $configMenu->id, 
                    'side' => MenuItem::ADMIN,
                    'route' => '/admin/default/site-configuration',
                    'position' => 1,
                    'css_class' => '',
                    'rule' => 'Yii::$app->user->can("ConfigEntry-view")',
                ));
                    $this->addEntry(new MenuItem(), array(
                        'name' => array('en' => 'Site', 'fr' => 'Site'),
                        'parent_id' => $site->id,
                        'side' => MenuItem::ADMIN,
                        'route' => '/admin/default/site-configuration',
                        'position' => 1,
                        'css_class' => '',
                        'rule' => 'Yii::$app->user->can("ConfigEntry-view")',
                    ));
                    $this->addEntry(new MenuItem(), array(
                        'name' => array('en' => 'Entries', 'fr' => 'Entrées'),
                        'parent_id' => $site->id,
                        'side' => MenuItem::ADMIN,
                        'route' => '/admin/config-entry',
                        'position' => 2,
                        'css_class' => 'advanced',
                        'rule' => 'Yii::$app->controller->adminDisplayMode === AdminParameters::ADVANCED_DISPLAY_MODE && Yii::$app->user->can("ConfigEntry-view")',
                    ));

                $this->addEntry(new MenuItem(), array(
                    'name' => array('en' => 'Menus', 'fr' => 'Menus'), 
                    'parent_id' => $configMenu->id, 
                    'side' => MenuItem::ADMIN,
                    'route' => '/admin/menus',
                    'position' => 2,
                    'css_class' => 'advanced',
                    'rule' => 'Yii::$app->controller->adminDisplayMode === AdminParameters::ADVANCED_DISPLAY_MODE && Yii::$app->user->can("MenuItem-view")',
                ));

                $this->addEntry(new MenuItem(), array(
                    'name' => array('en' => 'URL Rewriting Rules', 'fr' => 'Règles de Réécriture d\'URLs'), 
                    'parent_id' => $configMenu->id, 
                    'side' => MenuItem::ADMIN,
                    'route' => '/admin/url-rewriting-rule',
                    'position' => 4,
                    'css_class' => 'advanced',
                    'rule' => 'Yii::$app->controller->adminDisplayMode === AdminParameters::ADVANCED_DISPLAY_MODE && Yii::$app->user->can("UrlRewritingRule-view")',
                ));

                $this->addEntry(new MenuItem(), array(
                    'name' => array('en' => 'Log', 'fr' => 'Journal'), 
                    'parent_id' => $configMenu->id, 
                    'side' => MenuItem::ADMIN,
                    'route' => '/admin/log',
                    'position' => 5,
                    'css_class' => 'advanced',
                    'rule' => 'Yii::$app->controller->adminDisplayMode === AdminParameters::ADVANCED_DISPLAY_MODE && Yii::$app->user->can("ActiveRecordLogEntry-view")',
                ));
            
            // Task menu
            $this->addEntry(new MenuItem(), array(
                'name' => array('en' => 'Tasks', 'fr' => 'Tâches'), 
                'parent_id' => $adminMenu->id, 
                'side' => MenuItem::ADMIN,
                'route' => '/admin/default/tasks',
                'position' => 6,
                'css_class' => 'advanced',
                'rule' => 'Yii::$app->controller->adminDisplayMode === AdminParameters::ADVANCED_DISPLAY_MODE && Yii::$app->user->can("Tasks-manage")',
            ));
                            
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";

        ####################### PERMISSIONS #######################

        echo "    > creating permissions ...";
        $time = microtime(true);
        
        if(!is_dir(Yii::getAlias('@app/rbac')))
        {
            mkdir(Yii::getAlias('@app/rbac'));
        }
        
        // Create a permission to let users manage Yiingine blocking.
        $yiingineBlockBypass = $this->createPermission('YiingineBlockBypass', Yii::tA(array('en' => 'Lets a user bypass Yiingine blocking mechanisms.', 'fr' => 'Permet à un utilisateur de passer le mécanisme de blockage du Yiingine.')));
        
        // Create a permission for managing tasks.
        $this->createPermission('Tasks-manage', Yii::tA(array('en' => 'Lets a user run and see tasks.', 'fr' => 'Permet à un utilisateur d\'éxécuter et d\'afficher les tâches.')));

        // Create a permission for updating the site configuration.
        $this->createPermission('SiteConfiguration-update', Yii::tA(array('en' => 'Lets a user update the site configuration.', 'fr' => 'Permet à un utilisateur de modifier la configuration du site.')));

        // Create a permission to see active record log entries.
        $this->createPermission('ActiveRecordLogEntry-view', Yii::tA(array('en' => 'Lets a user view active record log entries.', 'fr'=> 'Permet à un utilisateur de visualiser les entrées de journal.')));

        // Create default roles.
        $this->createRole('APIUser', Yii::tA(array('en' => 'Role with full unrestricted access to the API.', 'fr' => 'Rôle pourvu d\'un accès illimité vers l\'API.')));
        Yii::$app->authManager->addChild($this->createRole('Administrator', Yii::tA(array('en' => 'Role with full unrestricted access to the administration interface and the API.', 'fr' => 'Rôle pourvu d\'un accès illimité vers l\'interface d\'administration et l\'API.'))), $yiingineBlockBypass);
        
        $this->createModelPermissions('Yiingine', array('ConfigEntry', 'MenuItem'));
        
        echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";
        
        ####################### SOCIAL METAS #######################
        
        echo "    > creating social metas ...";
        $time = microtime(true);
        
        $this->addEntry(new ConfigEntry(), ['name' => 'yiingine.SocialMetas.meta_description', 'value' => ['en' => 'Default description of the site', 'fr' => 'Description du site par défaut'], 'translatable' => 1]);
        $this->addEntry(new ConfigEntry(), ['name' => 'yiingine.SocialMetas.meta_keywords', 'value' => ['en' => 'defaultkeyword1, defaultkeyword2, defaultkeyword3', 'fr' => 'motclépardéfaut1, motclépardéfaut2, motclépardéfaut3'], 'translatable' => 1]);

        $this->addEntry(new ConfigEntry(), ['name' => 'yiingine.SocialMetas.meta_thumbnail', 'value' => 'test-default-image.jpg', 'translatable' => 0]);
        $assets = dirname(__FILE__).'/_'.get_class($this).'_assets';
        
        // If the destination directory does not exist.
        if(!is_dir(Yii::getAlias('@webroot/user/assets')))
        {
            mkdir(Yii::getAlias('@webroot/user/assets'));
        }
            
        copy($assets.'/test-default-image.jpg', Yii::getAlias('@webroot/user/assets').'/test-default-image.jpg');
        
        echo " done (time: ".sprintf('%.3f', microtime(true)-  $time)."s)\n";
    }

    /** Applies the logic to be executed when removing a migration.
     * @return boolean if the migration can be removed. */
    public function down() 
    {
        echo "m000000_000001_Yiingine does not support migration down.\n";
        return false;
    }
}
