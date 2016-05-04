<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$columns = [
    // id*
    [
        'class' => '\yiingine\grid\PositionColumn',
    ],
    [
        'class'=>'\yiingine\grid\MixedColumn',
        'header' => $model->getAttributeLabel('gallery_title'),
        'options' => ['style '=> 'height:70px;'],
        'columns' => [
            [
                'attribute' => 'gallery_title',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) 
                {
                    return '<b>'.str_replace('h1', 'p', $model->gallery_title).'</b><br />';
                },
                'headerOptions' => ['width' => 250]
            ],
            '\yiingine\modules\media\grid\AssociatedStatsColumn',
        ],
    ]
    // ts_updt*
    // buttons*      
];

// * : automaticaly set in engine/views/admin/model/index

// Ordering
$dataProvider = $model->search(Yii::$app->request->queryParams);
$dataProvider->query->orderBy(['position' => SORT_ASC]);

echo $this->render('//admin/model/index', [
    'model' => $model, 
    'columns' => $columns,
    'linkButton' => '$model->getUrl()',
    'dataProvider' => $dataProvider
]);
