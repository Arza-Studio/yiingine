<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\models\MenuItem;

$columns = [
    [
        'class' => '\yiingine\grid\PositionColumn',
        'query' => function($query, $model)
        {
            $query->where(['parent_id' => $model->parent_id]);
        }
    ],
    [
        'class' => '\yiingine\grid\MixedColumn',
        'columns' => [
            [
                'attribute' => 'parent_id',
                'filter' => \yiingine\widgets\admin\MenuTree::widget([
                    'withMenuItems' => true, 
                    'model' => $model, 
                    'attribute' => 'parent_id',
                    'options' => [
                        'class' => 'form-control', 
                        'prompt' => '',
                        /* Because of a bug with yii, selecting the empty value does not trigger a change
                         * to refresh the grid view so we have to do it manually.*/
                        'onclick' => 'if(!$(this).val()) $("#MenuItem-grid").yiiGridView("applyFilter");'
                    ]
                ]),
                'headerOptions' => ['width' => 100],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)
                {
                    return $model->parent_id ? '<b>'.MenuItem::makeNameUserFriendly($model->parent->name).'</b> > ' : ' ' ;
                }
            ],
            [
                'attribute' => 'name',
                'headerOptions' => ['width' => 100],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)
                {
                    $name = MenuItem::makeNameUserFriendly($model->name);
                    return $model->parent_id ? $name : '<b>'.$name.'</b>' ;
                }
            ]
        ],
    ],
    [
        'class' => '\yiingine\grid\MixedColumn',
        'columns' => [
            [
                'attribute' => 'route',
                'headerOptions' => ['width' => 100, 'style' => 'text-align:center;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)
                {
                    $value = ($model->route) ? '<a href="'.$model->getUrl().'" target="_blank">'.$model->route : '';
                    $value .= ($model->parameters) ? '' : '</a>';
                    return $value;
                },
            ],
            [
                'attribute' => 'parameters',
                'headerOptions' => ['width' => 100, 'style' => 'text-align:center;'],
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column)
                {
                    return '<span style="color:#999;">'.$model->parameters.'</span></a>';
                },
            ],
        ],
    ],
    [
        'attribute' => 'displayed',
        'class' => '\yiingine\grid\BooleanColumn'
    ],
    [
        'attribute' => 'side',
        'headerOptions' => ['width' => 75, 'style' => 'text-align:center;'],
        'contentOptions' => ['width' => 75, 'style' => 'text-align:center;'],
        'filter' => [
            MenuItem::ADMIN => MenuItem::sideIdToName(MenuItem::ADMIN),
            MenuItem::SITE => MenuItem::sideIdToName(MenuItem::SITE)
        ],
        'filterInputOptions' => [
            'prompt' => MenuItem::sideIdToName(MenuItem::ALL),
            'class' => 'form-control'
        ],
        'value' => function ($model, $key, $index, $column)
        {
            return MenuItem::sideIdToName($model->side);
        },
    ]
];
        
echo $this->render('//admin/model/index', [
    'model' => $model,
    'columns' => $columns,
    'displayWarning' => true
]);

