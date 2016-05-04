<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$data = [];
foreach($this->context->module->factory->getTypes() as $type => $manager)
{
    $data[$type] = $type;
}

$columns = [
    // id*
    [
        'class' => '\yiingine\grid\MixedColumn',
        'options' => ['style' => 'height:60px;'],
        'columns' => [
            [
                'attribute' => 'form_group_id',
                'headerOptions' => ['width' => 120, 'style' => 'text-align:center;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)
                {
                    return '<span style="text-align:center;text-transform:uppercase;font-size:10px;color:gray;">'.($model->formGroup ? $model->formGroup->name : Yii::t('generic', 'None')).'</span><br />';
                },
                'filter' => false,
            ],
            [
                'headerOptions' => ['width' => 120, 'style' => 'text-align:center;'],
                'attribute' => 'title',
                'format' => 'raw',
                'class' => '\yiingine\grid\TranslatableAttributeColumn',
                'value' => function ($model, $key, $index, $column)
                {
                    return '<b>'.$model->title.'</b>';
                },
            ],
            [
                'headerOptions' => ['width' => 120, 'style' => 'text-align:center;'],
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)
                {
                    return '<em>'.$model->name.'</em>&nbsp;';
                },
            ],
            [
                'headerOptions' => ['width' => 120, 'style' => 'text-align:center;'],
                'filter' => $data,
                'attribute' => 'type',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)
                {
                    return '('.$model->formName().')';
                },
            ],
        ],
    ],
    [
        'attribute' => 'position',
        'headerOptions' => ['width' => 50],
        'options' => ['style' => 'text-align:center;'],
    ],
];

foreach($this->context->module->getFieldParameters() as $name => $param)
{
    $this->params['paramName'] = $param->name;
    
    $columns[] = [
        'attribute' => $param->name,
        'headerOptions' => ['width' => 90, 'style' => 'text-align:center;'],
        'options' => ['style' => 'text-align:center;'],
        'value' => function ($model, $key, $index, $column)
        {
            return str_replace(",", ", ", $model->{$this->params['paramName']});
        },
    ];
    
    break; // Only display the first parameter.
}

$columns[] = [
    'class' => '\yiingine\grid\BooleanColumn',
    'attribute' => 'required'
];

// enable*
// ts_updt*
// buttons*

// * : automaticaly set in yiingine/views/admin/model/index

// Ordering
$dataProvider = $model->search(Yii::$app->request->queryParams);
$dataProvider->query->with('formGroup')->orderBy(['form_group_id' => SORT_ASC, 'position' => SORT_ASC]);
$dataProvider->getModels(); // Prefetch the models to prevent anything from modifying CustomField::$module.

echo $this->render('//admin/model/index', [
    'model' => $model, 
    'columns' => $columns,
    'displayWarning' => true,
    'deleteVisible' => '!$model->protected',
    'dataProvider' => $dataProvider
]);
