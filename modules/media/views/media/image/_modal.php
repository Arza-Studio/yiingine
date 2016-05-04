<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;

$model = $this->context->model;

# Image
$imageSrc = $model->getThumbnail();
$imageHtml = Html::img($imageSrc, ['class' => 'img-responsive', 'alt' => $model->getTitle()]);

$this->context->layout = $model->getTitle() ? '{header}{image}{content}{footer}' : '{image}{content}{footer}';

$this->context->layoutItems = ['{image}' => $imageHtml];

echo $this->context->view->render('@yiingine/modules/media/widgets/views/_modal.php');
