<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
return [
    'type' => 'form',
    'elements' => [
        'warning' => [ //Display a warning if the user is editing a system generated rule.
            'title' => Yii::t('generic', 'Warning').' !',
            'type' => 'fieldset',
            'elements' => [
                '<div style="color:'.Yii::$app->adminPalette->get('AdminWarning').';font-size:2em;text-align:center;">'.Yii::t('\yiingine\controllers\admin\UrlRewritingRuleController', 'You are editing a system generated rule.').'</div>',
            ],
            'visible' => (boolean)$model->system_generated,
        ],
        'form' => [
            'type' => 'fieldset',
            'title' => '',
            'elements' => [
                [
                    'type' => 'group',
                    'title' => Yii::t('\yiingine\controllers\admin\UrlRewritingRuleController', 'Rule'),
                    'elements' => [
                        'mode' => [
                            'type' => 'dropdownlist',
                            'items' => [
                                0 => Yii::t('\yiingine\controllers\admin\UrlRewritingRuleController', 'Parsing and creation'),
                                \yii\web\UrlRule::PARSING_ONLY => Yii::t('\yiingine\controllers\admin\UrlRewritingRuleController', 'Parsing only'),
                                \yii\web\UrlRule::CREATION_ONLY => Yii::t('\yiingine\controllers\admin\UrlRewritingRuleController', 'Creation only'),
                            ]
                        ],
                        'encode_params' => [
                            'type' => 'checkbox',
                        ],
                        'languages' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255
                        ]
                    ]
                ],
                [
                    'type' => 'group',
                    'title' => Yii::t('\yiingine\controllers\admin\UrlRewritingRuleController', 'Pattern'),
                    'elements' => [
                        'host' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255
                        ],
                        'pattern' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255
                        ]
                    ]
                ],
                [
                    'type' => 'group',
                    'title' => Yii::t('\yiingine\controllers\admin\UrlRewritingRuleController', 'Route'),
                    'elements' => [
                       'route'  => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255
                        ],
                        'defaults' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 255
                        ],
                        'suffix' => [
                            'type' => 'text',
                            'size' => 31,
                            'maxlength' => 31
                        ],
                        'verb' => [
                            'type' => 'text',
                            'size' => 31,
                            'maxlength' => 31
                        ],
                    ],
                ],
                [
                    'type' => 'group',
                    'title' => Yii::t('\yiingine\controllers\admin\UrlRewritingRuleController', 'Controls'),
                    'elements' => [
                        'enabled' => [
                            'type' => 'checkbox',
                        ],
                        'position' => [
                            'type' => '\yiingine\widgets\admin\PositionManager',
                        ]
                    ]
                ],
            ],
        ],
    ]
];
