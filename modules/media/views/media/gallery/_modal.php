<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\web\View;

$model = $this->context->model;

$this->context->layout = $model->getTitle() ? '{header}{slider}{content}{footer}' : '{slider}{content}{footer}';
$this->context->layoutItems = ['{slider}' => $this->render('_slider', ['model' => $model, 'sliderId' => $this->context->id.'Slider'])];

echo $this->context->view->render('@yiingine/modules/media/widgets/views/_modal.php');

$this->registerJs(new \yii\web\JsExpression('
    $("#'.$this->context->id.'").on("shown.bs.modal", function()
    {
        $("#'.$this->context->id.'Slider .slick-dots").remove(); // Bug : https://github.com/kenwheeler/slick/issues/1772
        $("#'.$this->context->id.'Slider").slick("reinit");
    });'
), \yii\web\View::POS_READY);
