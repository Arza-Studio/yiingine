<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$columns = [
    [
        'attribute' => 'user_id',
        'options' => ['width' => '50'],
        'format' => 'Integer'
    ],
    [
        'attribute' => 'user_name',
        'options' => ['width' => '150'],
    ],
    [
        'attribute' => 'action',
        'options' => ['width' => '75'],
        'filter' => ['CREATE' => 'CREATE', 'UPDATE' => 'UPDATE', 'DELETE' => 'DELETE']
    ],
    [
        'attribute' => 'model',
        'options' => ['width' => '100'],
    ],
    [
        'attribute' => 'model_id',
        'options' => ['width' => '50'],
    ],
    [
        'attribute' => 'model_title',
        'options' => ['width' => '100'],
    ],
    [
        'attribute' => 'datetime',
        'options' => ['width' => '80','style' => 'text-align:center;', 'class' => 'timestamp'],
        'format' => 'datetime'
    ]
];

echo $this->render('//admin/model/index', [
    'model' => $model, 
    'columns' => $columns,
    'linkButton' => '$model->model_admin_url ? Yii::$app->request->baseUrl.$model->model_admin_url : false'
]);
