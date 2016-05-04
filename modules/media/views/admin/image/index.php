<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$columns = [
    // id*
    [
        'attribute' => 'image_image',
        'header' => 'Image',
        'class' => '\yiingine\modules\media\grid\ImageColumn',
        'headerOptions' => ['width' => 90, 'style' => 'text-align:center;'],
        'options' => ['style' => 'text-align:center;height:98px;vertical-align:middle;'],
    ],
    [
        'class' => '\yiingine\grid\MixedColumn',
        'options' => ['style' => 'height:70px;'],
        'columns' => [
            [
                'attribute' => 'image_title',
                'headerOptions' => ['width' => 150],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)
                {
                    return '<b>'.str_replace('h1', 'p', $model->image_title).'</b><br />';
                }
            ],
            [
                'class' => '\yiingine\modules\media\grid\ImageInfoColumn',
                'attribute' => 'image_image',
                'headerOptions' => ['width' => 150],
            ]
        ]
    ]
    // ts_updt*
    // buttons*
];

// * : automaticaly set in engine/protected/views/admin/model/index

echo $this->render('//admin/model/index', [
    'model' => $model, 
    'columns' => $columns,
    'linkButton' => 'false'
]);
