<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$columns = [
    // id*
    [
        'attribute' => 'thumbnail',
        'headerOptions' => ['width' => 90, 'style' => 'text-align:center;'],
        'options' => ['style' => 'text-align:center;height:98px;vertical-align:middle;'],
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column)
        {
            return \yii\helpers\Html::tag('img', '', ['src' => $model->getThumbnail(), 'alt' => '-', 'width' => 60]);
        },
        'filter' => false,
        'enableSorting' => false,
    ],
    [
        'attribute' => 'document_title',
        'headerOptions' => ['width' => 150],
    ]
    // ts_updt*
    // buttons*
];

// * : automaticaly set in engine/protected/views/admin/model/index

echo $this->render('//admin/model/index', [
    'model' => $model, 
    'columns' => $columns,
    'linkButton' => 'Yii::$app->request->baseUrl.$model->getManager("document_file")->getFileUrl($model)',
]);
