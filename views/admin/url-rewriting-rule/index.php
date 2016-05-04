<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$columns = [
    [
        'attribute' => 'pattern',
        'headerOptions' => ['width' => 180]
    ],
    [
        'attribute' => 'route',
        'headerOptions' => ['width' => 180]
    ],
    [
        'attribute' => 'defaults',
        'headerOptions' => ['width' => 180]
    ],
    [
        'attribute' => 'languages',
        'headerOptions' => ['width' => 180]
    ],
    [
        'attribute' => 'enabled',
        'class' => '\yiingine\grid\BooleanColumn'
    ],
];

echo $this->render('//admin/model/index', [
    'model' => $model, 
    'columns' => $columns,
    'displayWarning' => true,
    'noDisplayColumn' => ['position'],
]);
