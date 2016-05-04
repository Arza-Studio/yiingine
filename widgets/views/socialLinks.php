<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;

# Html
foreach($links as $network => $data)
{
    echo Html::a(FA::icon($data['icon']), $data['url'], $data['options']);
}
