<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\modules\users\models\User;

$columns = [
    // id*
    [
        'attribute' => 'username',
        'headerOptions' => ['width' => 150]
    ],
    [
        'attribute' => 'email',
        'headerOptions' => ['width' => 150]
    ],
    [
        'attribute' => 'lastvisit',
        'headerOptions' => ['width' => 100],
        'format' => ['date', 'php:Y-m-d H:i:s']
    ],
    [
        'attribute' => 'status',
        'headerOptions' => ['width' => 100],
        'value' => function ($model, $key, $index, $column)
        {
            return \yiingine\modules\users\models\User::getStatusLabel($model->status);
        },
        'filter' => 
        [
            User::STATUS_NOACTIVE => User::getStatusLabel(User::STATUS_NOACTIVE),
            User::STATUS_ACTIVE => User::getStatusLabel(User::STATUS_ACTIVE),
            User::STATUS_BANNED => User::getStatusLabel(User::STATUS_BANNED), 
        ]
    ],
    [
        'attribute' => 'superuser',
        'headerOptions' => ['width' => 40, 'style' => 'text-align:center;'],
        'class' => '\yiingine\grid\BooleanColumn'
    ],
    // ts_updt*
    // buttons*
];

// * : automaticaly set in engine/views/admin/model/index

echo $this->render('//admin/model/index', [
    'model' => $model, 
    'columns' => $columns,
    'displayWarning' => true,
    'linkButton' => '$model->getUrl()',
    'deleteVisible' => 'Yii::$app->user->getIdentity()->id !== $model->id' // A user should not be able to delete his own model.
]);
