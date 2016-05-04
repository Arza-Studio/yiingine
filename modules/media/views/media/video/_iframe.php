<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;

/** @var $boolean if content should be lazy loaded.*/
if(!isset($lazyLoad)) $lazyLoad = false;

# Iframe
$iframe = $model->video_iframe;
// Check the existence of the iframe tag.
if($iframeDom = \keltstr\simplehtmldom\SimpleHTMLDom::str_get_html($iframe)->find('iframe', 0))
{   
    $iframeHtml = $iframe;
    // Optimize iframe.
    $iframeHtml = \yiingine\widgets\OptimizeIframes::widget([
        'html' => $iframeHtml,
        'returnIframesOnly' => true,
        'options' => ['class' => 'embed-responsive-item'],
        'layout' => Html::tag('div', '{iframe}', ['class' => 'embed-responsive embed-responsive-16by9'])
    ]);
    
    if($lazyLoad)
    {
        // Lazy loaded iframe
        $iframeHtml = \yiingine\widgets\LazyLoad::widget([
            'html' => $iframeHtml,
        ]);
    }
    
    // Overlay + MagnificPopup
    \roman444uk\magnificPopup\MagnificPopupAsset::register($this);
    $src = str_replace('https://www.youtube.com/embed/','http://www.youtube.com/watch?v=', $iframeDom->getAttribute('src'));
    \yiingine\widgets\Overlay::widget([
        'selector' => '#'.$this->context->id.' .iframe',
        'content' => FA::icon('play')->size(FA::SIZE_4X),
        'onClick' => 'obj.overlay.magnificPopup({items: [{"src":"'.$src.'", "type":"iframe"}]});',
    ]);
}
else
{
    $iframeHtml = Yii::t(\yiingine\modules\media\widgets\Thumbnail::className(), 'No iframe tag has been found.');
}

echo $iframeHtml;
