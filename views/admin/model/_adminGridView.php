<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\widgets\Pjax;

$dataProvider->getModels(); // Fetch the model to update the page count.

Pjax::begin([
    'timeout' => 30000 // Timeout has to be very high because queries can take a long time.
]);

if($dataProvider->pagination->pageSizeLimit !== false) // If changing the page size is enabled.
{
    $pagination = $dataProvider->pagination;
    $page = $pagination->page;
    
    echo \yii\helpers\Html::dropDownList('', $pagination->createUrl($page, $pagination->pageSize), [
            $pagination->createUrl($page, 10) => 10,
            $pagination->createUrl($page, 20) => 20,
            $pagination->createUrl($page, 50) => 50,
            $pagination->createUrl($page, 100) => 100
        ],
        // Fill the href attribute of a hidden link to trigger a page change using PJax
        [
            'onchange' => '$("#page-size-changer").attr("href", $(this).val()).click();',
            'class' => 'form-control',
            'style' => 'float:left; margin-bottom: 10px; width: 100px;',
            'title' => Yii::t('generic', 'Show')
        ]
    ).\yii\helpers\Html::a('', '', ['id' => 'page-size-changer', 'style' => 'display:hidden']);
}

echo \yii\grid\GridView::widget([
    'id' => $model->formName().'-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    //'enableHistory' => true,
    'columns' => $columns,
    'options' => ['class' => 'table table-striped table-hover'],
    'summaryOptions' => ['style' => 'float:right;'],
    'layout' => "{summary}\n{items}\n<div style=\"text-align: center;\">{pager}</div>"
]);

Pjax::end();
