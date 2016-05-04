<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\modules\users\modules\rbac\models;

return [
    'type' => 'form', 
    'elements' => [
        [
            'type' => 'fieldset',
            'title' => models\Assignment::getModelLabel(),
            'elements' => [
                [
                    'title' => models\Assignment::getModelLabel(),
                    'type' => 'group',
                    'elements' => [
                        'name' => [
                            'type' => 'text',
                            'size' => 20,
                            'readonly' => !$model->isNewRecord,
                            'forceVisible' => true
                        ],
                       'userId' => [
                               'type' => 'text',
                            'size' => 4,
                            'maxlength' => 11,
                            'readonly' => !$model->isNewRecord,
                            'forceVisible' => true
                        ],
                    ]
                ],
            ], // end groups
        ],
    ]
];
