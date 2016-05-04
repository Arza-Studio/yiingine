<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

// Columns
$columns = array();

// id*

$columns[] = array( //Add it automatically.
    'class'=>'CMixedColumn',
    'columns' => array(
        array(
            'class' => 'CDataColumn',
            'header' => Yii::tA(array('en'=>'Title', 'fr'=>'Titre')),
            'headerHtmlOptions' => array('width' => 300),
            'type' => 'raw',
            'value' => '"<b>".$data->getTitle()."</b>";',
        ),
    ),
);

// enable*
// ts_updt*
// buttons*

// * : automaticaly set in engine/views/admin/model/index

$this->renderPartial('//admin/model/index', array(
    'model' => $model, 
    'columns' => $columns,
    'linkButton' => 'false',
));
