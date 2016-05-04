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

if($model->image_link) // Link
{
    $linkHtml = Html::a(FA::icon('link'), $model->image_link, [
        'class' => 'btn'
    ]);
}
else // Expand
{
    $imageUrl = Url::to($model->getManager('image_image')->getFileUrl(), true);
    $expandHtml = Html::a(FA::icon('expand'), $imageUrl, [
        'id' => $this->context->id.'ExpandBtn',
        'class' => 'btn btn-primary'
    ]);
    \roman444uk\magnificPopup\MagnificPopup::widget([
        'target' => '#'.$this->context->id.'ExpandBtn'
    ]);
}

$this->context->imageLinkUrl = $model->image_link;
$this->context->footerLayout = $model->image_link ? '{link}{share}{url}' : '{expand}{share}{url}';
$this->context->footerLayoutItems = $model->image_link ? ['{link}' => $linkHtml] : ['{expand}' => $expandHtml];

echo $this->context->view->render('@yiingine/modules/media/widgets/views/_thumbnail.php');
