<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\web\View;

// Add an "update" button to the center buttons.
$this->params['centerButtons'][] = \yii\helpers\Html::a(Yii::t('generic', 'Update'), '', [
    'class' => 'btn',
    'title' => Yii::t('generic', 'Update'),
    // Submit and remove the window.onbeforeunloadevent because the user has clicked submit.
    'onclick' => "window.onbeforeunload = null;$('.form').find('form').submit();return false;",
]);

$this->params['breadcrumbs'][] = Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Application Configuration');

// Process the list of available languages into a list formatted for use with a checkbox list.
$languages = [];
foreach(array_flip(Yii::$app->params['app.supported_languages']) as $name => $value)
{
    // Translate the languages code to its name.
    $languages[$name] = extension_loaded('intl' ) ? Locale_get_display_language($name): $name;
}

$form = [
    'type' => 'form',
    'elements' => [
        // Website / Application
        [
            'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Web application'),
            'type' => 'fieldset',
            'elements' => [
                // Application
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Titles'),
                    'collapsed' => true,
                    'elements' => [
                        'app.name' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255,
                            'translatable' => true,
                            'visible' => isset(Yii::$app->params['app.name']),
                            'translatable' => $model->isTranslatable('app.name'),
                        ],
                        'app.catchphrase' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255,
                            'visible' => isset(Yii::$app->params['app.catchphrase']),
                            'translatable' => $model->isTranslatable('app.catchphrase'),
                        ]
                    ],
                ],
                // Languages
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Languages'),
                    'collapsed' => true,
                    'visible' => count(Yii::$app->params['app.supported_languages']) > 1,
                    'elements' => [
                        'app.available_languages' => [
                            'translatable' => false,
                            'type' => 'checkboxlist',
                            'items' => $languages
                        ]
                    ]
                ],
                // Graphic elements
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Graphic elements'),
                    'collapsed' => true,
                    'elements' => [
                        // Main logo
                        'app.main_logo' => [
                            'type' => '\yiingine\widgets\admin\FileListUploader',
                            'directory' => Yii::getAlias('@webroot/user/assets'),
                            'maxNumberOfFiles' => 1,
                            'allowedExtensions' => ['jpg', 'png', 'svg', 'gif'],
                            'visible' => isset(Yii::$app->params['app.main_logo']),
                        ],
                        // Main logo reduced
                        'app.main_logo_reduced' => [
                            'type' => '\yiingine\widgets\admin\FileListUploader',
                            'directory' => Yii::getAlias('@webroot/user/assets'),
                            'maxNumberOfFiles' => 1,
                            'allowedExtensions' => ['jpg', 'png', 'svg', 'gif'],
                            'visible' => isset(Yii::$app->params['app.main_logo_reduced']),
                        ],
                        // Favicon
                        'app.favicon' => [
                            'type' => '\yiingine\widgets\admin\FileListUploader',
                            'directory' => Yii::getAlias('@webroot/user/assets'),
                            'maxNumberOfFiles' => 1,
                            'allowedExtensions' => ['jpg', 'png', 'svg', 'gif'],
                            'visible' => isset(Yii::$app->params['app.favicon']),
                        ],
                        // Apple touch icon
                        'app.apple_touch_icon' => [
                            'type' => '\yiingine\widgets\admin\FileListUploader',
                            'directory' => Yii::getAlias('@webroot/user/assets'),
                            'maxNumberOfFiles' => 1,
                            'allowedExtensions' => ['png'],
                            'visible' => isset(Yii::$app->params['app.apple_touch_icon']),
                        ],
                        // Default background image
                        'app.default_background' => [
                            'type' => '\yiingine\widgets\admin\FileListUploader',
                            'directory' => Yii::getAlias('@webroot/user/assets'),
                            'maxNumberOfFiles' => 1,
                            'allowedExtensions' => ['jpg', 'png'],
                            'visible' => isset(Yii::$app->params['app.default_background']),
                        ],
                    ]
                ],
                // Default social metas
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Default social metas'),
                    'collapsed' => true,
                    'visible' => Yii::$app->has('socialMetas'),
                    'elements' => [
                        'yiingine.SocialMetas.default_thumbnail' => [
                            'type' => '\yiingine\widgets\admin\FileListUploader',
                            'directory' => Yii::getAlias('@webroot/user/assets'),
                            'maxNumberOfFiles' => 1,
                            'allowedExtensions' => ['jpg', 'png'],  
                        ],
                        'yiingine.SocialMetas.meta_keywords' => [
                            'type' => 'textarea',
                            'cols' => 70,
                            'rows' => 2,
                            'translatable' => $model->isTranslatable('yiingine.SocialMetas.meta_keywords'),
                        ],
                        'yiingine.SocialMetas.meta_description' => [
                            'type' => 'textarea',
                            'cols' => 70,
                            'rows' => 3,
                            'translatable' => $model->isTranslatable('yiingine.SocialMetas.meta_description'),
                        ],
                    ]
                ],
                // Domains
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Domains'),
                    'collapsed' => true,
                    'advanced'=> true,
                    'elements' => [
                        'app.main_domain' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255,
                        ],
                        'app.alternate_domains' => [
                            'type' => 'textarea',
                            'cols' => 70,
                            'rows' => 2,
                        ],
                    ]
                ],
                // Users
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Users'),
                    'collapsed' => true,
                    'advanced'=> true,
                    'elements' => [
                        'app.session_timeout' => [
                            'type' => 'text',
                            'size' => 10,
                            'maxlength' => 10,
                        ],
                        'yiingine.users.disable_user_registration' => [
                            'type' => 'checkbox',
                            'layout' => "{input}\n{label}\n{hint}\n{error}",
                        ],
                        'yiingine.users.disable_user_accounts' => [
                            'type' => 'checkbox',
                            'layout' => "{input}\n{label}\n{hint}\n{error}",
                        ],
                    ]
                ],
                // Framework
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Framework'),
                    'collapsed' => true,
                    'advanced'=> true,
                    'elements' => [
                        'app.system_email' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255,
                        ],
                        'app.require_javascript' => [
                            'type' => 'checkbox',
                            'layout' => "{input}\n{label}\n{hint}\n{error}",
                        ],
                        'app.incompatible_browsers' => [
                            'type' => 'textarea',
                            'cols' => 70,
                            'rows' => 2,
                        ],
                        'ajaxNavigation_dot_enabled' => [
                            'type' => 'checkbox',
                            'layout' => "{input}\n{label}\n{hint}\n{error}",
                        ],
                    ]
                ]
            ],
        ],
        // Owner
        [
            'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Owner'),
            'type' => 'fieldset',
            'elements' => [
                // Brand
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Brand / Company / Corporate'),
                    'collapsed' => true,
                    'elements' => [
                        'app.brand_name' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255,
                            'visible' => isset(Yii::$app->params['app.brand_name']),
                            'translatable' => $model->isTranslatable('app.brand_name'),
                        ],
                        'app.brand_logo' => [
                            'type' => '\yiingine\widgets\admin\FileListUploader',
                            'directory' => Yii::getAlias('@webroot/user/assets'),
                            'maxNumberOfFiles' => 1,
                            'allowedExtensions' => ['jpg', 'png', 'svg', 'gif'],
                            'visible' => isset(Yii::$app->params['app.brand_logo']),
                        ],
                        'yiingine.SocialMetas.meta_copyright' => [
                            'type' => 'textarea',
                            'cols' => 70,
                            'rows' => 1,
                            'visible' => Yii::$app->has('socialMetas'),
                            'translatable' => $model->isTranslatable('yiingine.SocialMetas.meta_copyright'),
                        ],
                        
                    ]
                ],
                // Owner
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Owner'),
                    'collapsed' => true,
                    'elements' => [
                        'app.owner_name' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255,
                            'visible' => isset(Yii::$app->params['app.owner_name']),
                        ],
                        'app.owner_last_name' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255,
                            'visible' => isset(Yii::$app->params['app.owner_last_name']),
                        ],
                    ]
                ],
                // Address
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Address'),
                    'collapsed' => true,
                    'elements' => [
                        'app.owner_street' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255,
                            'visible' => isset(Yii::$app->params['app.owner_street']),
                        ],
                        'app.owner_city' => [
                            'type' => 'text',
                            'size' => 30,
                            'maxlength' => 31,
                            'visible' => isset(Yii::$app->params['app.owner_city']),
                        ],
                        'app.owner_postal_code' => [
                            'type' => 'text',
                            'size' => 10,
                            'maxlength' => 31,
                            'visible' => isset(Yii::$app->params['app.owner_postal_code']),
                        ],
                        'app.owner_country' => [
                            'type' => 'text',
                            'size' => 30,
                            'maxlength' => 31,
                            'visible' => isset(Yii::$app->params['app.owner_country']),
                            'translatable' => $model->isTranslatable('app.country_translations'),
                        ],
                    ]
                ],
                // Contact
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Contact'),
                    'collapsed' => true,
                    'elements' => [
                        'app.owner_telephone1' => [
                            'type' => 'text',
                            'size' => 20,
                            'maxlength' => 31,
                            'visible' => isset(Yii::$app->params['app.owner_telephone1']),
                        ],
                        'app.owner_telephone2' => [
                            'type' => 'text',
                            'size' => 20,
                            'maxlength' => 31,
                            'visible' => isset(Yii::$app->params['app.owner_telephone2']),
                        ],
                        'app.owner_fax' => [
                            'type' => 'text',
                            'size' => 20,
                            'maxlength' => 31,
                            'visible' => isset(Yii::$app->params['app.owner_fax']),
                        ],
                        'app.owner_email1' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255,
                            'visible' => isset(Yii::$app->params['app.owner_email1']),
                        ],
                        'app.owner_email2' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255,
                            'visible' => isset(Yii::$app->params['app.owner_email2']),
                        ],
                    ]
                ],
            ]
        ],
        // External Tools
        [
            'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'External tools'),
            'type' => 'fieldset',
            'elements' => [
                // Google
                [
                    'type' => 'group',
                    'title' => 'Google',
                    'collapsed' => true,
                    'advanced'=> true,
                    'elements' => [
                        'app.google_analytics_key' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255,
                            'visible' => isset(Yii::$app->params['app.google_analytics_key']),
                        ]
                    ]
                ],
                // Microsoft
                [
                    'type' => 'group',
                    'title' => 'Microsoft',
                    'collapsed' => true,
                    'advanced'=> true,
                    'elements' => [
                        'app.bing_app_id' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255,
                            'visible' => isset(Yii::$app->params['app.bing_app_id']),
                        ],
                    ],
                ],
                // Facebook
                [
                    'type' => 'group',
                    'title' => 'Facebook',
                    'collapsed' => true,
                    'advanced'=> true,
                    'elements' => [
                        'app.facebook_admin_id' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255,
                            'visible' => isset(Yii::$app->params['app.facebook_admin_id']),
                        ],
                    ],
                ],
            ],
        ],
        // Maintenance
        [
            'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Maintenance'),
            'type' => 'fieldset',
            'advanced' => true,
            'elements' => [
                // Annoucement
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Annoucement'),
                    'collapsed' => true,
                    'advanced'=> true,
                    'elements' => [
                        'app.announcement' => [
                            'type' => 'textarea',
                            'cols' => 70,
                            'rows' => 2,
                            'translatable' => $model->isTranslatable('app.announcement'),
                        ]
                    ]
                ],
                // Database
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Database'),
                    'collapsed' => true,
                    'advanced'=> true,
                    'elements' => [
                        'app.read_only' => [
                            'type' => 'checkbox',
                            'layout' => "{input}\n{label}\n{hint}\n{error}",
                        ],
                    ],
                ],
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Closure'),
                    'collapsed' => true,
                    'advanced'=> true,
                    'elements' => [
                        'app.emergency_maintenance_mode.enabled' => [
                            'type' => 'checkbox',
                            'layout' => "{input}\n{label}\n{hint}\n{error}",
                        ],
                    ],
                ],
            ],
        ],
    ]
];


// CountChar for meta_description
$countChar = '';
foreach(Yii::$app->params['app.supported_languages'] as $language)
{   
    $countChar .= \yiingine\widgets\CountChar::widget([
        'inputSelector' => \yii\helpers\Html::getInputId($model, 'yiingine.SocialMetas.meta_description'.($language == Yii::$app->getBaseLanguage()? '': '_'.$language)),
        'warningLimit' => 150,
        'errorLimit' => 200,
        'locked' => false,
    ]);
}
$form['elements'][] = $countChar;

echo $this->render('//admin/model/_form', ['model' => $model, 'form' => $form]);
