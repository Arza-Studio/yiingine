<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use \yii\helpers\Url;
use rmrevin\yii\fontawesome\FA;

$model = $this->context->model;

# File
$fileUrl = Url::to($model->getManager('document_file')->getFileUrl(), true);
$fileIcon = $model->getManager('document_file')->getFaIcon();
$fileHtml = Html::a(FA::icon($fileIcon), $fileUrl, [
    'class' => 'btn btn-primary',
    'target' => '_blank'
]);

// We use {image} in layout only if the document model provide it's own thumbnail.
$this->context->layout = $model->getThumbnail() ? '{header}{image}{description}{footer}' : '{header}{description}{footer}';
$this->context->imageOverlayContent = FA::icon($fileIcon)->size(FA::SIZE_4X);
$this->context->footerLayout = '{file}{share}{url}';
$this->context->footerLayoutItems = ['{file}' => $fileHtml];

echo $this->context->view->render('@yiingine/modules/media/widgets/views/_thumbnail.php');
