<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$structure = \yiingine\modules\customFields\managers\Base::renderInputs($model);

// Wrap the first level in a fieldset.
$structure[1] =  [
    'type' => 'fieldset',
    'title' => Yii::t(\yiingine\modules\media\web\admin\MediumController::className(), '{type} FIELDS', ['type' => $model->getModelLabel()]),
    'elements' => $structure[1] ? $structure[1] : [Yii::t(\yiingine\modules\media\web\admin\MediumController::className(), '{type} does not have custom fields defined.', ['type' => $model->type])]
];

// If there is more than one view, show a select, otherwise hide this attribute the model will set the default view during validation.
if(is_array($model->getViews()) && count($model->getViews()) > 1)
{
    $data = [];
    
    // For each configured view
    foreach($model->getViews() as $view)
    {
        $data[$view['path']] = Yii::tA($view['title']).'<div class="hint" style="margin-bottom:5px;">'.Yii::tA($view['description']).'</div>';
    }
    
    $structure[2][] = 
    [
        'type' => 'fieldset',
        'title' => Yii::t('generic', 'Display'),
        'elements' => [
            [
                'title' => Yii::t(\yiingine\modules\media\web\admin\MediumController::className(), 'VIEWS'),
                'type' => 'group',
                'elements' => [
                    'view' => [
                        'type' => 'radiolist',
                        'uncheckValue' => null,
                        'items' => $data,
                        'separator' => '',
                        'encode' => false
                    ]
                ],
                'collapsed' => true
            ]
        ]
    ];
}
// Else the medium is being rendered by a field or simply has no view.

return $structure;
