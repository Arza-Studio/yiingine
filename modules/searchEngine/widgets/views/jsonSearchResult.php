<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

$data = [];

// Build a JSON array in the format accepted by the jquery-ui autocomplete widget.
foreach($result->getModels() as $item)
{
    $data[] = [
        'value' => $item[1]->id.':'.get_class($item[1]), 
        'label' => $item[1]->getDescriptor()
    ];
}

return $data;
