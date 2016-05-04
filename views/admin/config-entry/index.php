<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$columns = [
    [
        'attribute' => 'name',
        'options' => [
            'style' => 'font-weight:bold;',
            'width' => 200,
        ],
    ],
    [
        'attribute' => 'value',
        'options' => ['width' => 200],
        'value' => function ($model, $key, $index, $column)
        {
            return implode("<br />", \yiingine\libs\Functions::mb_str_split($model->value, 40));
        },
        'isTranslatable' => function($model){ return $model->translatable; },
        'class' => '\yiingine\grid\TranslatableAttributeColumn',
        'format' => 'raw'
    ],
];

if(count(Yii::$app->params['app.supported_languages']) > 1) // If configuration entries can be translated.
{
    $columns[] = [
        'attribute' => 'translatable',
        'options' => [
            'class' => 'enable',
            'width' => 120,
            'style' => 'text-align:center;',
        ],
        'class' => '\yiingine\grid\BooleanColumn',
    ];
}

echo $this->render('//admin/model/index', [
    'model' => $model,
    'columns' => $columns,
    'displayWarning' => true
]);
