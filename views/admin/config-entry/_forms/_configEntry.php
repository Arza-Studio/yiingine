<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

return [
    'type' => 'fieldset',
    'title' => $model->getModelLabel(),
    'elements' => [
        [
            'type' => 'group',
            'title' => Yii::t(\yiingine\controllers\admin\ConfigEntryController::className(), 'Data'),
            'elements' => [
                'name' => [
                    'type' => 'text',
                    'size' => 60,
                    'maxlength' => 255,
                ],
                'value' => [
                    'type' => 'textarea',
                    'rows' => 6,
                    'cols' => 50,
                    'id' => 'value',
                    //'togglable' => true,
                    'translatable' => $model->translatable,
                ],
            ],
        ],
        [
            'type' => 'group',
            'title' => Yii::t(\yiingine\controllers\admin\ConfigEntryController::className(), 'Configuration'),
            'elements' => [
                'translatable' => [
                    'type' => 'checkbox',
                    'toggle' => 'value',
                ]
            ],
            'visible' => count(Yii::$app->getParameter('app.supported_languages')) > 1
        ]
    ],
];
