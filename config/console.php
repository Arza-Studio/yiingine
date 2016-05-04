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
    ],
    'controllerNamespace' => '\app\controllers',
    'controllerMap' => [
        'database' => '\yiingine\console\controllers\DatabaseController',
        'message' => '\yiingine\console\controllers\MessageController',
        'migrate' => [
            'class' => '\yiingine\console\controllers\MigrateController',
            'migrationPath' => '@yiingine/migrations'
        ],
        'tasks' => '\yiingine\console\controllers\TasksController'
    ],
    'components' => [
        'i18n' => ['translations' => ['*' => '\yiingine\i18n\PhpMessageSource']],
        'db' => [
            'class' => '\yii\db\Connection',
            'emulatePrepare' => true,
            'charset' => 'utf8',
            'schemaCacheDuration' => 3600 * 24 * 365, // A year.
        ],
        'authManager' => ['class' => '\yii\rbac\PhpManager'],
        'DbBackupTask' => ['class' => '\yiingine\tasks\DBBackupTask'],
        'ActiveRecordLogArchivingTask' => ['class' => '\yiingine\tasks\ActiveRecordLogArchivingTask'],
    ],
    'modules' => [ 
        'media' => [ 
            'class' => '\yiingine\modules\media\Module',
            'label' => 'Media',
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

return $yiingineConfig;
