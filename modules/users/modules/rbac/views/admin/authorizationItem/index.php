<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;

$this->title = Yii::t(\yiingine\web\admin\ModelController::className(), 'Manage').' - '.$model->getModelLabel();

$columns = [
    [
        'attribute' => 'name',
        'headerOptions' => ['width' => 150],
    ],
    [
        'attribute' => 'description',
        'headerOptions' => ['width' => 300],
    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{update}{delete}{view}',
        'headerOptions' => ['width' => 70, 'class' => 'button-column'],
        'options' => ['class' => 'button-column'],
        'buttons' => [ // Cannot modify the role 'Administrator'
            'view' => function($url, $model, $key)
            {
                return $model->name == 'Administrator' ? Html::a('', ['view', 'id' => $model->getId()], ['title' => Yii::t('generic', 'View'), 'class' => 'view btnFa fa fa-dot-circle-o commentedBtn']) : '';
            },
            'delete' => function($url, $model, $key)
            {
                return $model->name != 'Administrator' ? Html::a('', ['delete', 'id' => $model->getId()], ['title' => Yii::t('generic', 'Update'), 'class' => 'view btnFa fa fa-trash noLoader']) : '';
            },
            'update' => function($url, $model, $key)
            {
                return $model->name != 'Administrator' ? Html::a('', ['update', 'id' => $model->getId()], ['title' => Yii::t('generic', 'Update'), 'class' => 'view btnFa fa fa-pencil']) : '';
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
