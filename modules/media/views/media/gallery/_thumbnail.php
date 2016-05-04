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

# Layout
if(isset($line) && $line)
{
    $layout = '<div class="container-fluid"><div class="row-eq-height"><div class="col-sm-4">{slider}</div><div class="col-sm-8">{header}{description}{footer}</div></div></div>';
}
else
{
    $layout = '{header}{slider}{description}{footer}';
}
if(!$model->gallery_title)
{
    $layout = str_replace('{header}', '', $layout);
}

# Expand
$expandHtml = Html::button(FA::icon('expand'), [
    'id' => $this->context->id.'ExpandBtn',
    'class' => 'btn btn-primary'
]);
$magnificPopupItems = [];
foreach($model->gallery_items as $item)
{
    if($item->type == 'app\modules\media\models\Image')
    {
        $magnificPopupItems[] = [
            'src' => Url::to($item->getManager('image_image')->getFileUrl(), true),
            'type' => 'image'
        ];
    }
    elseif($item->type == 'app\modules\media\models\Video')
    {
        if($src = \keltstr\simplehtmldom\SimpleHTMLDom::str_get_html($item->video_iframe)->find('iframe', 0)->getAttribute('src'))
        { 
            $src = str_replace('https://www.youtube.com/embed/','http://www.youtube.com/watch?v=', $src);
            $magnificPopupItems[] = [
                'src' => $src,
                'type' => 'iframe',
            ];
        }
    }
}
\roman444uk\magnificPopup\MagnificPopup::widget([
    'target' => '#'.$this->context->id.'ExpandBtn',
    'options' => [
        'items' => $magnificPopupItems,
        'gallery' => [
            'enabled' => true
        ],
        //'index' => '$("#'.$this->context->id.'Slider").slick("slickCurrentSlide") }'
    ]
]);

$this->context->layout = $layout;
$this->context->layoutItems = [
    '{slider}' => $this->render('_slider', [
        'sliderId' => $this->context->id.'Slider', 
        'model' => $model
    ])
];
$this->context->footerLayout = '{expand}{share}{url}';

$this->context->footerLayoutItems = ['{expand}' => $expandHtml];

echo $this->context->view->render('@yiingine/modules/media/widgets/views/_thumbnail.php');
