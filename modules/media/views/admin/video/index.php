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
        'attribute' => 'thumbnail',
        'class' => '\yiingine\modules\media\grid\ImageColumn',
        'headerOptions' => ['width' => 90, 'style' => 'text-align:center;'],
        'options' => ['style' => 'text-align:center;height:98px;vertical-align:middle;'],
    ],
    [
        'attribute' => 'video_title',
        'headerOptions' => ['width' => 250],
    ]
    // ts_updt*
    // buttons*      
];

// * : automaticaly set in engine/protected/views/admin/model/index

// Ordering
$dataProvider = $model->search(Yii::$app->request->queryParams);
$dataProvider->query->orderBy(['position' => SORT_ASC]);

echo $this->render('//admin/model/index', [
    'model' => $model, 
    'columns' => $columns,
    'linkButton' => '$model->getUrl()',
    'dataProvider' => $dataProvider
]);
