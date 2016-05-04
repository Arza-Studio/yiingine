<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

# ASSETS URL
$assetsUrl = Yii::$app->assetManager->publish(Yii::getAlias('@app/modules/media/assets/media'))[1];

# CSS
$this->registerCssFile($assetsUrl.'/insert/_column.css');
$css = '
.insertColumn { margin-top:10px;
                background-color: '.Yii::$app->palette->get('Gray', 80).'; }
.insertColumn h2 { color: '.Yii::$app->palette->get('Gray', -30).'; }
';
$this->registerCss($css, ['media' => 'screen']);

$columns = [
    // id*
    [
        'class' => '\yiingine\grid\MixedColumn',
        'header' => $model->getAttributeLabel('insert_content'),
        'columns' => [
            [
                'attribute' => 'insert_content',
                'headerOptions' => ['width' => 300],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)
                {
                    return '<div class="insertColumn">'.$model->insert_content.'</div>';
                }
            ],
        ],
    ]
    // ts_updt*
    // buttons*
];

// * : automaticaly set in engine/views/admin/model/index

echo $this->render('//admin/model/index', [
    'model' => $model, 
    'columns' => $columns,
    'linkButton' => 'false',
]);
