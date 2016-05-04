<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use \yii\helpers\Url;
use \yii\web\View;
use \yiingine\modules\media\widgets\Thumbnail;
use rmrevin\yii\fontawesome\FA;

// To make access to variables easier.
extract(get_object_vars($this->context));
$id = $this->context->id;

# Admin Overlay
\yiingine\widgets\admin\AdminOverlay::widget([
    'selector' => '#'.$id,
    'model' => $model
]);

# Article
echo Html::beginTag('article',  array_merge_recursive([
    'id' => $id,
    'class' => 'thumbnail',
    'data-type' => lcfirst($model->formName())
], $this->context->options));

$strings = [];

# Header
if(strpos($layout, '{header}') !== false)
{
    $headerHtml = Html::tag($this->context->headerTag, $model->getTitle(), ['class' => 'title']);
    $strings['{header}'] = Html::tag('header', $headerHtml);
}

# Image
if(strpos($layout, '{image}') !== false && $model->getThumbnail())
{
    $imageSrc = $model->getThumbnail();
    $imageHtml = Html::img($imageSrc, ['alt' => $model->getTitle()]);
    $imageLinkUrl = $this->context->imageLinkUrl;
    if($imageLinkUrl || $model->getViews()) // If the image must be wrapped with a link.
    {
        $url = ($imageLinkUrl) ? $imageLinkUrl : $model->getUrl();
        $imageHtml = Html::a($imageHtml, $url);
        if($imageOverlay)
        {
            \yiingine\widgets\Overlay::widget([
                'selector' => '#'.$id.' .image img',
                'content' => ($imageOverlayContent) ? $imageOverlayContent : FA::icon('link')->size(FA::SIZE_4X),
                'onClick' => '$("#'.$id.' .image a")[0].click();', // Don't know why we must use [0].click() instead of trigger("click") to reach click event here ?
            ]);
        }
    }
    elseif($this->context->imageZoom) // If the image must use a zoom on click event.
    {
        $imageHtml = Html::a($imageHtml, $imageSrc);
        \roman444uk\magnificPopup\MagnificPopup::widget([
            'target' => '#'.$id.' .image a',
            'options' => [
                'zoom' => [
                    'enabled' => true,
                ]
            ]
        ]);
        if($imageOverlay)
        {
            \yiingine\widgets\Overlay::widget([
                'selector' => '#'.$id.' .image img',
                'content' => ($imageOverlayContent) ? $imageOverlayContent : FA::icon('expand')->size(FA::SIZE_4X),
                'onClick' => '$("#'.$id.' .image a").trigger("click");',
            ]);
        }
    }
    if($imageOptimizeImgs) // If the image must be optimized.
    {
        $optimizeImgsParams = [
            'html' => $imageHtml,
            'unwrapParagraph' => true,
            'options'=> [
                'class' => 'img-responsive'
            ]
        ];
        if($imageOptimizeImgs === Thumbnail::RATIO_1BY1 ||
           $imageOptimizeImgs === Thumbnail::RATIO_4BY3 ||
           $imageOptimizeImgs === Thumbnail::RATIO_16BY9)
        {
            $optimizeImgsParams['options'] = [
                'class' => 'embed-responsive-item',
                'style'=>'object-fit:cover;'
            ];
            $optimizeImgsParams['layout'] = Html::tag('div', '{img}', [
                'class' => 'embed-responsive embed-responsive-'.$imageOptimizeImgs
            ]);
        }
        $imageHtml = \yiingine\widgets\OptimizeImgs::widget($optimizeImgsParams);
    }
    if($imageLazyLoad) // If the image must be lazy loaded.
    {
        $imageHtml = \yiingine\widgets\LazyLoad::widget([
            'html' => $imageHtml,
        ]);
    }
    $strings['{image}'] = Html::tag('div', $imageHtml, ['class' => 'image']);
}

# Description
if(strpos($layout, '{description}') !== false)
{
    $descriptionHtml = $model->getDescription();
    if($descriptionHtml != '')
    {
        if($descriptionTruncate) // If the description must be truncated.
        {
            $descriptionHtml = \yiingine\widgets\Truncate::widget([
                'html' => $descriptionHtml,
                'length' => $descriptionTruncate,
                'removeFormating' => true
            ]);
        }
        if($descriptionLazyLoad) // If the img and iframe tags in description must be lazy loaded.
        {
            $descriptionHtml = \yiingine\widgets\LazyLoad::widget([
                'html' => $descriptionHtml,
            ]);
        }
        if($descriptionOptimizeImgs) // If the image must be optimized.
        {
            $optimizeImgsParams = [
                'html' => $descriptionHtml,
                'unwrapParagraph' => true,
                'options'=> [
                    'class' => 'img-responsive'
                ]
            ];
            if($descriptionOptimizeImgs === Thumbnail::RATIO_1BY1 ||
               $descriptionOptimizeImgs === Thumbnail::RATIO_4BY3 ||
               $descriptionOptimizeImgs === Thumbnail::RATIO_16BY9)
            {
                $optimizeImgsParams['options'] = [
                    'class' => 'embed-responsive-item',
                    'style'=>'object-fit:cover;'
                ];
                $optimizeImgsParams['layout'] = Html::tag('p', '{img}', [
                    'class' => 'embed-responsive embed-responsive-'.$descriptionOptimizeImgs
                ]);
            }
            $descriptionHtml = \yiingine\widgets\OptimizeImgs::widget($optimizeImgsParams);
        }
        if($descriptionOptimizeIframes) // If the image must be optimized.
        {
            $optimizeIframesParams = [
                'html' => $descriptionHtml
            ];
            if($descriptionOptimizeIframes === Thumbnail::RATIO_1BY1 ||
               $descriptionOptimizeIframes === Thumbnail::RATIO_4BY3 ||
               $descriptionOptimizeIframes === Thumbnail::RATIO_16BY9)
            {
                $optimizeIframesParams['options'] = [
                    'class' => 'embed-responsive-item',
                    'style'=>'object-fit:cover;'
                ];
                $optimizeIframesParams['layout'] = Html::tag('p', '{iframe}', [
                    'class' => 'embed-responsive embed-responsive-'.$descriptionOptimizeIframes
                ]);
            }
            $descriptionHtml = \yiingine\widgets\OptimizeIframes::widget($optimizeIframesParams);
        }
        $strings['{description}'] = Html::tag('div', $descriptionHtml, ['class' => 'description']);
    }
    else
    {
       $strings['{description}'] = '';
    }
}

# Footer
if(strpos($layout, '{footer}') !== false)
{
    $footerStrings = [];
    // Url
    if(strpos($footerLayout, '{url}') !== false)
    {
        if(strpos(Url::to($model->getUrl()), '?modal=') !== false)
        {
            $modalId = uniqid();
            
            // If the model is not providing a url we use a modal.
            $footerUrlHtml = Html::button(FA::icon('plus'), [
                'data-toggle' => 'modal',
                'data-target' => '#'.$modalId,
                'aria-expanded' => 'false',
                'class' => 'btn btn-primary'
            ]);
            
            $modal = \yiingine\modules\media\widgets\Modal::widget([
               'id' => $modalId,
               'model' => $model,
               'parameters' => ['headerDisplay' => isset($headerHtml)],
               'forceGenericView' => false
            ]);
        }
        else
        {
            $footerUrlHtml = Html::a(FA::icon('arrow-right'), Url::to($model->getUrl()), ['class' => 'btn btn-primary']);
        }
        $footerStrings['{url}'] = $footerUrlHtml;
    }
    // Share links
    if(strpos($footerLayout, '{share}') !== false)
    {
        $footerShareHtml = \yiingine\widgets\ShareBox::widget([
            'type' => \yiingine\widgets\ShareBox::POPOVER,
            'url' => Url::to($model->getUrl(), true),
            'title' => $model->getTitle(),
            'description' => $model->getDescription()
        ]);
        $this->registerJs('
        $(".shareBoxPopover").on("shown.bs.popover", function(){
            $(".popover .share .btn").hover(
                function(){
                    $(this).css({background:$(this).data("color"),color:"white"});
                },
                function(){
                    $(this).removeAttr("style");
                }
            );
        });', View::POS_READY);
        $footerStrings['{share}'] = $footerShareHtml;
    }
    // Additional footer items
    if(!empty($footerLayoutItems))
    {
        foreach($footerLayoutItems as $item => $html)
        {
            $footerStrings[$item] = $html;
        }
    }
    $footerHtml = strtr($footerLayout, $footerStrings);
    $strings['{footer}'] = Html::tag('footer', $footerHtml);
}

# Additional items
if(!empty($layoutItems))
{
    foreach($layoutItems as $item => $html)
    {
        $strings[$item] = $html;
    }
}

echo strtr($layout, $strings);

echo Html::endTag('article');

if(isset($modal))
{
    echo $modal;
}
