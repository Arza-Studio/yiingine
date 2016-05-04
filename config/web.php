<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$yiingineConfig = [
    'id' => 'please_set_an_application_id',
    'name' => 'Please set a name for this application',
    'bootstrap' => [
        'log',
        '\yiingine\behaviors\ApplicationParametersBehavior',
        '\yiingine\behaviors\ApplicationRedirectBehavior',
        '\yiingine\behaviors\ApplicationLanguageBehavior',
        '\yiingine\behaviors\ApplicationBlockBehavior'
    ],
    'controllerNamespace' => '\app\controllers',
    'components' => [
        'assetManager' => [
            /* Link the assets instead of copying them to the assets folder, this way,  
             * assets are always consistend with their source. Since this is an extra
             * level of indirection (slower), requires configuration (Options FollowSymLinks
             * in apache) and may be a security risk, it should only be enabled in development
             * mode.*/
            'linkAssets' => YII_DEBUG,
            'dirMode' => 0755, // drwxr-xr-x+
            'fileMode' => 0755, // -rwxr-xr-x+
        ],
        'view' => [
            'class' => '\yiingine\web\View',
            'siteTheme' => '\app\themes\base\Theme',
            'adminTheme' => '\yiingine\themes\admin\base\Theme',
        ],
        'user' => [
            'class' => 'yiingine\modules\users\web\User',
            'enableAutoLogin' => true, // Enable cookie-based authentication.
            'enableSession' => true, // Presist logging across requests.
            'loginUrl' => ['/users/default/admin-login'],
            'authTimeout' => YII_DEBUG ? 30 * 60 : 2 * 60 * 60, // Default timeout of 2 hours.
            'identityClass' => 'yiingine\modules\users\models\User',
        ],
        'authManager' => ['class' => '\yii\rbac\PhpManager'],
        'i18n' => ['translations' => ['*' => '\yiingine\i18n\PhpMessageSource']],
        'urlManager' => [
            'class' => 'yiingine\web\UrlManager',
            //'urlFormat' => 'path',
            'showScriptName' => false,
            'enablePrettyUrl' => true,
            //'caseSensitive' => true,
            'rules' => [
                'sitemap.xml' => 'site/sitemap-index',
                'robots.txt' => 'site/robots',
                '/admin/login' => 'users/default/admin-login',
                '/admin' => 'admin/default/index',
             ]
        ],
        'db' => [
            'class' => '\yii\db\Connection',
            'emulatePrepare' => true,
            'charset' => 'utf8',
            'schemaCacheDuration' => 3600 * 24 * 365, // A year.
            'enableSchemaCache' => true,
            'enableQueryCache' => false
        ],
        'errorHandler' => [
            'errorAction'=> 'site/error', // Use 'site/error' action to display errors
        ],
        'adminPalette' => [
            'class' => '\yiingine\base\Palette',
            'colors' => [
                'AdminDefault' => '#ff9900', // use for success
                'AdminError' => '#ff0000',
                'AdminWarning' => '#eeae01',
                'Gray' => '#71707e',
            ],
        ],
        'imagine' => ['class' => '\yiingine\base\Imagine'],
        'DbBackupTask' => ['class' => '\yiingine\tasks\DBBackupTask'],
        'ActiveRecordLogArchivingTask' => ['class' => '\yiingine\tasks\ActiveRecordLogArchivingTask'],
        'socialMetas' => ['class' => '\yiingine\web\SocialMetas']
    ],
    'modules' => [ 
        'media' => [ 
            'class' => 'yiingine\modules\media\Module',
            'label' => 'Media',
            'controllerNamespace' => '\app\modules\media\controllers',
            'enableModuleModel' => false,
            'modules' => [
                'mediaFields' => [
                    'class' => '\yiingine\modules\customFields\CustomFieldsModule',
                    'tableName' => 'media_media_fields',
                    'modelClass' => '\yiingine\modules\media\models\Medium',
                    'components' =>  [
                        'factory' => [
                            'class' => '\yiingine\modules\customFields\managers\Factory',
                            'managers' => [
                                'integer' => ['class' => '\yiingine\modules\customFields\managers\Integer'],
                                'varchar' => ['class' => '\yiingine\modules\customFields\managers\Varchar'],
                                'text' => ['class' => '\yiingine\modules\customFields\managers\Text'],
                                'file' => [
                                    'class' => '\yiingine\modules\customFields\managers\File',
                                    'directory' => function($manager)
                                    {
                                        return Yii::getAlias("@webroot/user/media/".$manager->owner->formName())."/".$manager->owner->primaryKey."/".$manager->getAttribute();
                                    }
                                ],
                                'image' => [
                                    'class' => '\yiingine\modules\customFields\managers\Image',
                                    'directory' => function($manager)
                                    {
                                        return Yii::getAlias("@webroot/user/media/".$manager->owner->formName())."/".$manager->owner->primaryKey."/".$manager->getAttribute();
                                    }
                                ],
                                'enum' => ['class' => '\yiingine\modules\customFields\managers\Enum'],
                                'fkey' => ['class' => '\yiingine\modules\customFields\managers\FKey'],
                                'color' => ['class' => '\yiingine\modules\customFields\managers\Color'],
                                'boolean' => ['class' => '\yiingine\modules\customFields\managers\Boolean'],
                                'float' => ['class' => '\yiingine\modules\customFields\managers\Float'],
                                'date' => ['class' => '\yiingine\modules\customFields\managers\Date'],
                                'dateTime' => ['class' => '\yiingine\modules\customFields\managers\DateTime'],
                                'html' => [
                                    'class' => '\yiingine\modules\customFields\managers\Html',
                                    'directory' => function($manager)
                                    {
                                        return Yii::getAlias("@webroot/user/media/".$manager->owner->formName())."/".$manager->owner->primaryKey."/".$manager->getAttribute();
                                    }
                                ],
                                'phpCode' => ['class' => '\yiingine\modules\media\managers\PhpCode'],
                                'oneToMany' => ['class' => '\yiingine\modules\customFields\managers\OneToMany'],
                                'manyToMany' => [
                                    'class' => '\yiingine\modules\customFields\managers\ManyToMany',
                                    'table' => 'media_media'
                                ],
                                'associatedMenuItems' => ['class' => '\yiingine\modules\media\managers\AssociatedMenuItems',],
                                'urlRewriting' => ['class' => '\yiingine\modules\media\managers\UrlRewriting'],
                                'mediaPosition' => ['class' => '\yiingine\modules\media\managers\MediaPosition'],
                            ]
                        ]
                    ],
                    'fieldParameters' => [
                        'owners' => [ 
                            'class' => '\yiingine\modules\media\parameters\Owners',
                            'name' => 'owners',
                        ],
                        'availability' => [ 
                            'class' => '\yiingine\modules\media\parameters\Availability',
                            'name' => 'availability',
                        ]
                    ]
                ]
            ]
        ],
        'users' => [ 
            'class' => '\yiingine\modules\users\Module',
            'cachingLevel' => 0,
            'defaultRoute' => 'profile',
            'modules' => [
                'profileFields' => [
                    'class' => '\yiingine\modules\customFields\CustomFieldsModule',
                    'tableName' => 'users_profiles_fields',
                    'modelClass' => '\yiingine\modules\users\models\User',
                    'components' =>  [
                        'factory' => [
                            'class' => '\yiingine\modules\customFields\managers\Factory',
                            'managers' => [
                                'integer' => ['class' => '\yiingine\modules\customFields\managers\Integer'],
                                'varchar' => ['class' => '\yiingine\modules\customFields\managers\Varchar'],
                                'text' => ['class' => '\yiingine\modules\customFields\managers\Text'],
                                'enum' => ['class' => '\yiingine\modules\customFields\managers\Enum'],
                                'color' => ['class' => '\yiingine\modules\customFields\managers\Color'],
                                'boolean' => ['class' => '\yiingine\modules\customFields\managers\Boolean'],
                                'float' => ['class' => '\yiingine\modules\customFields\managers\Float'],
                                'date' => ['class' => '\yiingine\modules\customFields\managers\Date'],
                                'datetime' => ['class' => '\yiingine\modules\customFields\managers\DateTime'],
                            ]
                        ]
                    ],
                    'fieldParameters' => [
                        'requirement' => [
                            'class' => '\yiingine\modules\users\parameters\Requirement',
                            'name' => 'requirement',
                            'required' => true
                        ],
                        'visible' => [
                            'class' => '\yiingine\modules\users\parameters\Visible',
                            'name' => 'visible',
                            'required' => true
                        ]
                    ]
                ]
            ]
        ],
    ],
    'params' => [
        'copyright' => 'ARZA STUDIO',
        'yiingine.admin.default_page_size' => 100,
        'app.special_users' => ['admin', 'administrator', 'administrateur', 'engine'],
        'app.incompatible_admin_clients' => ['windows' => ['ie4', 'ie5', 'ie7', 'ie8'], 'apple' => ['ie']]
    ],
    'sourceLanguage' => 'en',
    'language' => 'en'
];

/*if(!YII_DEBUG) // If Yii is not in debug mode.
{
    // Caches messages for a year.
    $yiingineConfig['components']['message']['cachingDuration'] = 3600 * 24 * 365;
}
else
{
    // Activate the debug controller.
    $yiingineConfig['controllerMap']['debug'] = ['class' => 'engine.controllers.DebugController'];
}*/

return $yiingineConfig;
