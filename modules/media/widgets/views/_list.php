<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;

$modelClass = $this->context->query->modelClass;
// $modelName = $modelClass::formName() does not work! No idea why...
$modelName = new $modelClass;
$modelName = $modelName->formName();

echo Html::beginTag('section', [
    'class' => 'list',
    'data-type' => lcfirst($modelName)
]);

foreach($dataProvider->getModels() as $model)
{
    echo \yiingine\modules\media\widgets\Thumbnail::widget([
        'model' => $model,
        'viewName' => $this->context->lineView,
        'parameters' => $this->context->lineViewParameters
    ]);
}

if($this->context->pagination) // If pagination has been enabled.
{
    echo \yii\widgets\LinkPager::widget(['pagination' => $dataProvider->pagination]);
}

echo Html::endTag('section');
