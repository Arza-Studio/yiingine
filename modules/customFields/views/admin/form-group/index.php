<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$columns = [
    // id*
    [
        'attribute' => 'name',
        'headerOptions' => ['width' => 340],
        'options' => ['style' => 'height:50px;'],
        'class' => '\yiingine\grid\TranslatableAttributeColumn',
    ],
    [
        'attribute' => 'position',
        'headerOptions' => ['width' => 50],
        'options' => ['style' => 'text-align:center;'],
    ],
    [
        'class' => '\yiingine\grid\BooleanColumn',
        'attribute' => 'collapsed'
    ]
];

// enable*
// ts_updt*
// buttons*

// * : automaticaly set in /yiingine/views/admin/model/index

// Filter form groups to only show those belonging to the current custom fields module.
$dataProvider = $model->search(Yii::$app->request->queryParams);
$dataProvider->query->addOrderBy('position')->andWhere(['owner' => $this->context->module->tableName]);

echo $this->render('//admin/model/index', [
    'model' => $model,
    'dataProvider' => $dataProvider,
    'columns' => $columns,
    'displayWarning' => true,
]);
