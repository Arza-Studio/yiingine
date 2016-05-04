<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

// Mixed Page + Module Data Column
$columns = [
    // id*
    [
        'class' => '\yiingine\grid\MixedColumn',
        'options' => ['style' => 'height:70px;'],
        'columns' => [
            [
                'attribute' => 'page_title',
                'headerOptions' => ['width' => 200],
                'format' => 'raw',
                'value' =>  function ($model, $key, $index, $column)
                {
                    return '<b>'.str_replace('h1', 'p', $model->page_title).'</b>';
                }, 
            ],
            [
                'class' => '\yiingine\modules\media\grid\MenuItemsInfoColumn',
                'headerOptions' => ['width' => 200 ],
            ],
            [
                'class' => '\yiingine\modules\media\grid\AssociatedStatsColumn'
            ]
        ],
    ],
    [
        'class' => '\yiingine\grid\BooleanColumn',
        'attribute' => 'enabled'
    ]

    // ts_updt*
    // buttons*
];

// * : automaticaly set in engine/views/admin/model/index

echo $this->render('//admin/model/index', [
    'model' => $model, 
    'columns' => $columns,
    'linkButton' => '$model->getUrl()',
    'deleteVisible' => '!$model->module_owner_id'
]);
