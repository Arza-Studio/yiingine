<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
use \yiingine\models\admin\AdminParameters;

# JAVASCRIPT
$this->registerJs(
    '// CONTENT CONTAINER WIDTH
    $("#contentContainer").css({width:618});
    // Re-Initialize admin content
    if(typeof initContent != "undefined")
    {
        initContent();
    }',
\yii\web\View::POS_READY);

$this->params['breadcrumbs'][] = Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Admin Preferences');

# FORM

$form = [
    'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Administration panel display parameters'),
    'elements' => [
        [
            'type' => 'group',
            'title' => Yii::t(\yiingine\controllers\admin\DefaultController::className(), 'Administration panel display mode'),
            'elements' => [
                'displayMode' => [
                    'type' => 'dropdownlist',
                    'items' => [
                        AdminParameters::NORMAL_DISPLAY_MODE => ucfirst(AdminParameters::getDisplayModeLabel(AdminParameters::NORMAL_DISPLAY_MODE)),
                        AdminParameters::ADVANCED_DISPLAY_MODE => ucfirst(AdminParameters::getDisplayModeLabel(AdminParameters::ADVANCED_DISPLAY_MODE))
                    ],
                ]
            ]
        ]
    ]
];

echo $this->render('//admin/model/_form', ['form' => $form, 'model' => $model]);
