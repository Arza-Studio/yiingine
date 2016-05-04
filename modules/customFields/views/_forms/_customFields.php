<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

// Initialize the structure with a group for fields without a defined group.
$structure = [
    1 => [ // First level.
        'noGroup' => [
            'type' => 'group',
            'title' => '',
            'elements' => [],
            'position' => 0
        ]
    ]
];

// Sort fields by group.
foreach($model->getFields(false) as $field)
{
    // If it is the first time this group is encountered.
    if($field->formGroup && !isset($structure[$field->formGroup->level][$field->formGroup->name]))
    {
        // Create that group.
        $structure[$field->formGroup->level][$field->formGroup->name] = [
            'title' => $field->formGroup->name,
            'type' => 'group',
            'elements' => [],
            'collapsed' => $field->formGroup->collapsed,
            'position' => $field->formGroup->position,
            'model' => $field->formGroup
        ];
    }
    
    // Force open the group if there is an error with one of its containing field.
    if($model->hasErrors($field->name) && $field->formGroup)
    {
        $structure[$field->formGroup->level][$field->formGroup->name]['collapsed'] = false;
    }
}

foreach($structure as $level)
{
    // Sort the form groups levels according to their position.
    uasort($level, function($a, $b){ return $a['position'] - $b['position']; });
}

$editLinkCondition = !Yii::$app->request->isAjax && 
Yii::$app->controller->getSide() === \yiingine\web\Controller::ADMIN &&
Yii::$app->controller->adminDisplayMode === \yiingine\models\admin\AdminParameters::ADVANCED_DISPLAY_MODE;

/* Define the variable form elements. For this loop to work correctly,
 * fields must be grouped by form group.*/
foreach($model->getFields(false) as $field) // Iterate through all custom fields.
{              
    if(!$field->in_forms) // If the field should not appear in forms.
    {
        continue; // Skip it.
    }
    
    $input = $model->getManager($field->name)->renderInput($model);
    
    // Display an edit link link beside the label of the custom field.
    if($field->isAccessible() && 
        is_array($input) && 
        !isset($input['label']) &&
        $editLinkCondition
    )
    {
        $input['label'] = $model->getAttributeLabel($field->name).($model->isAttributeRequired($field->name) ? '*': '');
        $url = \yii\helpers\Url::to($field->getAdminUrl());
        // Save the current url so it can be redirected to from the admin.
        $url .= (strpos($url, '&') !== false ? '&': '?').'returnUrl='.urlencode(Yii::$app->request->url);
        $input['label'] .= ' '.\yii\helpers\Html::a(\yii\helpers\Html::tag('span','', ['class' => 'noAjax fa fa-pencil']).$field->name, $url, ['class' => 'noAjax']);
    }
    
    // Add the profile form element to the form structure in its group.
    $structure[$field->formGroup ? $field->formGroup->level: 1][$field->formGroup ? $field->formGroup->name: 'noGroup']['elements'][$field->name] = $input;
}

// Delete groups that are empty.
foreach($structure as &$level)
{
    foreach($level as $name => $group)
    {
        if(empty($group['elements']))
        {
            unset($level[$name]);
        }
    }
}

// Build group tree.
foreach($structure as $number => $level)
{
    do 
    {
        $done = true;
        
        foreach($level as $name => $group)
        {
            if(isset($group['model']) && $group['model'])
            {
                $formGroup = $group['model'];
                
                if(!$formGroup->parent_id) // If the group does not have a parent.
                {
                    continue;
                }
                
                $formGroup = $formGroup->parent;
                
                if(!isset($structure[$formGroup->level][$formGroup->name]))
                {
                    $structure[$formGroup->level][$formGroup->name] = [
                        'title' => $formGroup->name,
                        'type' => 'fieldset',
                        'elements' => [],
                        //'collapsed' => $formGroup->collapsed,
                        'position' => $formGroup->position,
                        'model' => $formGroup
                    ];
                }
                
                $structure[$formGroup->level][$formGroup->name]['elements'][] = $group;
                unset($structure[$number][$name]);
                
                $done = false;
            }
        }
    }
    while(!$done);
}

return $structure;
