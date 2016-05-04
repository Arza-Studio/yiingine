<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\models\MenuItem;

return [
    'title' => 'Menu',
    'type' => 'fieldset',
    'elements' => [
        [
            'type' => 'group',
            'title' => Yii::t('\yiingine\controllers\admin\MenusController', 'Parent'),
            'elements' => [
                'parent_id' => [
                    'type' => '\yiingine\widgets\admin\MenuTree',
                    'options' => ['class' => 'form-control']
                ],
            ],
        ],
        [
            'type' => 'group',
            'title' => Yii::t('\yiingine\controllers\admin\MenusController', 'Name'),
            'elements' => [
               'name' => [
                       'type' => 'text',
                       'translatable' => true,
                       'size' => 60,
                       'maxlength' => 255
                ],
            ]
        ],
        [
            'type' => 'group',
            'title' => Yii::t('\yiingine\controllers\admin\MenusController', 'URL'),
            'elements' => [
                'route' => [
                    'type' => 'text',
                    'size' => 60,
                    'maxlength' => 255,
                ],
                'parameters' => [
                    'type' => 'text',
                    'size' => 60,
                    'maxlength' => 255,
                ],
                'arguments' => [
                    'type' => 'text',
                    'size' => 60,
                    'maxlength' => 255,
                ],
                'fragment' => [
                    'type' => 'text',
                    'size' => 60,
                    'maxlength' => 255,
                ],
            ]
        ],
        [
            'type' => 'group',
            'title' => Yii::t('\yiingine\controllers\admin\MenusController', 'Presentation'),
            'elements' => [
                'css_class' => [
                    'type' => 'text',
                    'size' => 60,
                    'maxlength' => 255,
                ],
                'target' => [
                    'type' => 'radiolist',
                    'items'=> [
                        '' => Yii::t('\yiingine\controllers\admin\MenusController', 'Opens the link in the same frame as it was clicked (default).'),
                        '_blank' => Yii::t('\yiingine\controllers\admin\MenusController', 'Opens the link in a new window or tab.'),
                        '_parent' => Yii::t('\yiingine\controllers\admin\MenusController', 'Opens the link in the parent frame.'),
                        '_top' => Yii::t('\yiingine\controllers\admin\MenusController', 'Opens the link in the full body of the window.'),
                    ],
                ]
            ]
        ],
        [
            'type' => 'group',
            'title' => Yii::t('\yiingine\controllers\admin\MenusController', 'Position'),
            'elements' => [
                'position' => [
                    'type' => '\yiingine\widgets\admin\PositionManager',
                    'relatedAttribute' => 'parent_id',
                ],
                'side' => [
                    'type' => 'dropdownlist',
                    'items' => [
                        MenuItem::ALL => MenuItem::sideIdToName(MenuItem::ALL),
                        MenuItem::ADMIN => MenuItem::sideIdToName(MenuItem::ADMIN),
                        MenuItem::SITE => MenuItem::sideIdToName(MenuItem::SITE),
                    ]
                ]
            ]
        ],
        [
            'type' => 'group',
            'title' => Yii::t('\yiingine\controllers\admin\MenusController', 'Controls'),
            'elements' => [
                'enabled' => [
                    'type' => 'checkbox',
                ],
                'displayed' => [
                    'type' => 'checkbox',
                ],
                'rule' => [
                    'type' => 'textarea',
                    'cols' => 70,
                    'rows' => 2
                ]
            ]
        ],
    ],
];
