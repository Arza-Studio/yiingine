<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\modules\customFields\models\FormGroup;

return [
    'type' => 'fieldset',
    'title' => FormGroup::getModelLabel(),
    'elements' => [
        [
            'type' => 'group',
            'title' => FormGroup::getModelLabel(),
            'elements' => [
                'name' => [
                    'type' => 'text',
                    'size' => 60,
                    'maxlength' => 255,
                    'translatable' => true
                ],
                'collapsed' => [
                    'type' => 'checkbox',
                ],
                'level' => [
                    'type' => 'text',
                    'size' => 10,
                    'maxlength' => 10,
                ],
                'parent_id' => [
                    'type' => 'text',
                    'size' => 10,
                    'maxlength' => 10,
                ],
                'position' => [
                    'type' => 'yiingine\widgets\admin\PositionManager',
                    'model' => $model,
                    'relatedAttribute' => 'owner',
                    'relatedValue' => $model->owner
                ]
            ]
        ]
    ]
];
