<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use \yii\helpers\Url;
use rmrevin\yii\fontawesome\FA;

/** @var array $attribute the attribute where the gallery images are. */
if(!isset($attribute)) $attribute = 'gallery_items';
/** @var mixed $embedResponsive string the css class if required, boolean to false if not. */
if(!isset($embedResponsive)) $embedResponsive = 'embed-responsive-4by3';
/** @var array $allowExpansion allow the expansion of the images if they are clicked */
if(!isset($allowExpansion)) $allowExpansion = true;
/** @var $boolean if content should be lazy loaded.*/
if(!isset($lazyLoad)) $lazyLoad = false;

# Slider
$sliderHtml = '';
if(count($model->$attribute))
{
    if(!isset($sliderId))
    {
        $sliderId = uniqid();
    }
    // Built items html.
    $itemsHtml = [];
    foreach($model->$attribute as $item)
    {
        // Only Image or Video models can be associated to galleries.
        if($item->type == app\modules\media\models\Image::className())
        {
            $src = Url::to($item->getManager('image_image')->getFileUrl(), true);
            
            $image = Html::img($src, ['alt' => $item->getTitle()]);
            
            if($lazyLoad)
            {
                $image = \yiingine\widgets\LazyLoad::widget([
                    'html' => $image,
                ]);
            }
            
            if($embedResponsive)
            {
                $itemsHtml[] = \yiingine\widgets\OptimizeImgs::widget([
                    'html' => $image,
                    'options' => ['class' => 'embed-responsive-item', 'style'=>'object-fit:cover;'],
                    'layout' => Html::tag('div', '{img}', ['class' => 'embed-responsive '.$embedResponsive])
                ]);
            }
            else
            {
                $itemsHtml[] = \yiingine\widgets\OptimizeImgs::widget([
                    'html' => $image,
                    'layout' => Html::tag('div', '{img}')
                ]);
            }
        }
        elseif($item->type == app\modules\media\models\Video::className())
        {
            $iframe = $item->video_iframe;
            
            if($lazyLoad)
            {
                $iframe = \yiingine\widgets\LazyLoad::widget([
                    'html' => $item->video_iframe,
                ]);
            }
            if($embedResponsive)
            {
                $itemsHtml[] = \yiingine\widgets\OptimizeIframes::widget([
                    'html' => $iframe,
                    'returnIframesOnly' => true,
                    'options' => ['class' => 'embed-responsive-item'],
                    'layout' => Html::tag('div', '{iframe}', ['class' => 'embed-responsive '.$embedResponsive])
                ]);
            }
            else
            {
                $itemsHtml[] = \yiingine\widgets\OptimizeIframes::widget([
                    'html' => $iframe,
                    'returnIframesOnly' => true,
                    'layout' => Html::tag('div', '{iframe}')
                ]);
            }
        }
    }        
    $sliderHtml = evgeniyrru\yii2slick\Slick::widget([
        // HTML tag for container. Div is default.
        'itemContainer' => 'div',
        // HTML attributes for widget container
        'containerOptions' => ['id' => $sliderId, 'class' => 'slider'],
        // Items for carousel. Empty array not allowed, exception will be throw, if empty 
        'items' => $itemsHtml,
        // HTML attribute for every carousel item
        'itemOptions' => ['class' => 'item'],
        // settings for js plugin
        // @see http://kenwheeler.github.io/slick/#settings
        'clientOptions' => [
            'infinite' =>  true,
            'autoplay' => false,
            'autoplaySpeed' => 3000,
            'speed' => 300,
            'slidesToShow' =>  1,
            'arrows' => false,
            'dots' => true,
        ]
    ]);
    
    if($allowExpansion)
    {
        // Overlay + MagnificPopup
        $magnificPopupItems = [];
        foreach($model->$attribute as $item)
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
        $magnificPopupItems = yii\helpers\Json::encode($magnificPopupItems, JSON_UNESCAPED_SLASHES);
        \yiingine\widgets\Overlay::widget([
            'selector' => '#'.$sliderId,
            'content' => FA::icon('expand')->size(FA::SIZE_4X),
            'onClick' => 'obj.overlay.magnificPopup({items: '.$magnificPopupItems.', gallery:{enabled:true}, index: $("#'.$sliderId.'").slick("slickCurrentSlide")});',
        ]);
    }
}

echo $sliderHtml;
