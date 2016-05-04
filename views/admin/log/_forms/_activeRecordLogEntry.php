<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

return [
    'title' => $model->getModelLabel(),
    'type' => 'fieldset',
    'elements' => [
        [
            'type' => 'group',
            'title' => Yii::t(\yiingine\controllers\admin\LogController::className(), 'User'),
            'elements' => [
                'user_id' => [
                    'type' => 'text',
                    'size' => 10,
                ],
                'user_name' => [
                    'type' => 'text',
                    'size' => 20,
                ],
            ],
        ],
        [
            'type' => 'group',
            'title' => Yii::t(\yiingine\controllers\admin\LogController::className(), 'Operation'),
            'elements' => [
                'action' => [
                    'type' => 'text',
                    'size' => 10,
                ],
                'datetime' => [
                    'type' => 'text',
                    'size' => 20,
                ],
            ],
        ],
        [
            'type' => 'group',
            'title' => Yii::t(\yiingine\controllers\admin\LogController::className(), 'Model'),
            'elements' => [
                'model' => [
                    'type' => 'text',
                    'size' => 20,
                ],
                'model_id'=> [
                    'type' => 'text',
                    'size' => 10,
                ],
                'model_table' => [
                    'type' => 'text',
                    'size' => 20,
                ],
                'model_title' => [
                    'type' => 'text',
                    'size' => 30,
                ],
                'model_admin_url' => [
                    'type' => 'text',
                    'size' => 60,
                ],
            ],
        ],
        [
            'type' => 'group',
            'title' => Yii::t(\yiingine\controllers\admin\LogController::className(), 'Attribute'),
            'visible' => $model->action == 'UPDATE',
            'elements' => [
                'attribute' => [
                    'type' => 'text',
                    'size' => 20,
                ],
                'previous_attribute_value' => [
                    'type' => 'textarea',
                    'cols' => 68,
                    'rows' => 5,
                ],
                'new_attribute_value' => [
                    'type' => 'textarea',
                    'cols' => 68,
                    'rows' => 5,
                ],
            ],
        ]
    ],
];
