<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;

$this->context->adminTitle = Yii::t(\yiingine\web\admin\ModelController::className(), 'Manage').' - '.$model->getModelLabel();

$columns = [
    [
        'attribute' => 'userId',
        'headerOptions' => ['width' => 300],
        'value' => function ($model, $key, $index, $column)
        {
            return $model->userId.' ('.\yiingine\modules\users\models\User::findOne($model->userId).')';
        } 
    ],
    [
        'attribute' => 'name',
        'headerOptions' => ['width' => 150],
    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{view}{update}{delete}',
        'headerOptions' => ['width' => 70, 'class' => 'button-column'],
        'options' => ['class' => 'button-column'],
        'buttons' => [
            // A user cannot modify its own assignments.
            'view' => function($url, $model, $key)
            {
                return $model->userId == Yii::$app->user->getIdentity()->id ? Html::a('', ['view', 'id' => $model->getId()], ['title' => Yii::t('generic', 'View'), 'class' => 'view btnFa fa fa-dot-circle-o commentedBtn']) : '';
            },
            'delete' => function($url, $model, $key)
            {
                return $model->userId != Yii::$app->user->getIdentity()->id  ? Html::a('', ['delete', 'id' => $model->getId()], ['title' => Yii::t('generic', 'Update'), 'class' => 'view btnFa fa fa-trash noLoader']) : '';
            },
            'update' => function($url, $model, $key)
            {
                return $model->userId != Yii::$app->user->getIdentity()->id  ? Html::a('', ['update', 'id' => $model->getId()], ['title' => Yii::t('generic', 'Update'), 'class' => 'view btnFa fa fa-pencil']) : '';
            }
        ]
    ]
];

echo $this->render('//admin/model/_adminGridView', [
    'columns' => $columns,
    'model' => $model,
    'dataProvider' => $model->search(),
    'pageSizeChangeable' => true,
    'prefix' => $model::formName()
]);
