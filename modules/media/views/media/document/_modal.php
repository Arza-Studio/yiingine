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

# Image
$imageSrc = $model->getThumbnail();
$imageHtml = Html::img($imageSrc, ['class' => 'img-responsive', 'alt' => $model->getTitle()]);
$imageHtml = Html::a($imageHtml, $fileUrl);

$this->context->layout = '{header}<div class="row"><div class="col-sm-4">{image}</div><div class="col-sm-8">{content}</div></div>{footer}';
$this->context->layoutItems = ['{image}' => $imageHtml];
$this->context->footerLayout = '{file}{close}';
$this->context->footerLayoutItems = ['{file}' => $fileHtml];

echo $this->context->view->render('@yiingine/modules/media/widgets/views/_modal.php');
