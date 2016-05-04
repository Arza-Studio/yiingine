<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

return [
    'type' => 'form', 
    'elements' => [
         [
            'type' => 'fieldset',
            'title' => $model->getModelLabel(),
            'elements' => [
                [
                    'title' => Yii::t(\yiingine\modules\users\modules\rbac\models\AuthorizationItem::className(), 'Name'),
                    'type' => 'group',
                    'elements' => [
                        'name' => [
                            'type' => 'text',
                            'size' => 20,
                        ],
                       'description' => [
                               'type' => 'textarea',
                            'cols' => 60,
                            'rows' => 2,
                        ],
                    ]
                ],
                 [
                    'title' => Yii::t(\yiingine\modules\users\modules\rbac\models\AuthorizationItem::className(), 'Rules'),
                    'type' => 'group',
                    'elements' => [     
                       'ruleName' => [
                               'type' => 'text',
                            'size' => 60,
                        ],
                        'data' => [
                               'type' => 'textarea',
                            'cols' => 60,
                            'rows' => 2,
                        ],
                    ],
                ], // end group
            ], // end groups
        ],
        [
            'type' => 'fieldset',
            'name' => Yii::t(\yiingine\modules\users\modules\rbac\models\AuthorizationItem::className(), 'Association'),
            'visible' => !$model->isNewRecord, // Item must exist before associations can be made.
            'elements' => [
                [
                    'type' => 'group',
                    'title' => Yii::t(\yiingine\modules\users\modules\rbac\models\AuthorizationItem::className(), 'Children'),
                    'elements' => [
                        'children' => [
                            'type' => '\yiingine\modules\users\modules\rbac\widgets\AuthorizationItemBrowser'
                        ]
                    ]
                ]
            ]
        ]
    ],
];
