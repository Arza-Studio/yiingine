<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;

# View options
/** @var boolean if the variables found in content must be replaced with runBeforeRender */
if(!isset($runBeforeRender)) $runBeforeRender = true;
/** @var boolean if the images found in content must be optimized */
if(!isset($optimizeImgs)) $optimizeImgs = true;
/** @var boolean if the iframes found in content must be optimized */
if(!isset($optimizeIframes)) $optimizeIframes = true;
/** @var boolean set true if the images or iframes found in content must be lazy loaded. */
if(!isset($lazyLoad)) $lazyLoad = true;
/** @var string the name of the content attribute. */
if(!isset($attribute)) $attribute = 'page_content';

# Data
// Page content
if(!isset($content)) $content = $model->$attribute;
// Page variables (before_render)
if($runBeforeRender)
{
    if(!isset($variables))
    {
        $variables = $this->context->runBeforeRender($model);
    }
    // If the runBeforeRender has not return false.
    if($variables)
    {
        // Replaces references to render variables in the content by the actual value of those variables.
        // A reference is written "{{$name}}".
        foreach($variables as $name => $value)
        {
            $content = str_replace('<p>{{$'.$name.'}}</p>', $value, $content);
            $content = str_replace('{{$'.$name.'}}', $value, $content);
        }
    }
}

# Html

$position = 0;
while(($position = strpos( $content, '{{gallery}}', $position)) !== false)
{
    $position += 11;
    $end = strpos($content, '{{/gallery}}', $position);
    $gallery = substr($content, $position, $end - $position);

    $content = substr_replace($content, evgeniyrru\yii2slick\Slick::widget([
        'itemContainer' => 'div',
        'containerOptions' => ['class' => 'slider'],
        'items' => \keltstr\simplehtmldom\SimpleHTMLDom::str_get_html($gallery)->find('img'),
        'itemOptions' => ['class' => 'item'],
        'clientOptions' => [
            'infinite' =>  true,
            'autoplay' => false,
            'autoplaySpeed' => 3000,
            'speed' => 300,
            'slidesToShow' =>  1,
            'arrows' => false,
            'dots' => true,
        ]
    ]), $position - 11, $end - $position + 23);
}

// If the iframes found in the description must be optimized.
if($optimizeIframes)
{
    $content = \yiingine\widgets\OptimizeIframes::widget([
        'html' => $content,
        'options' => ['class' => 'embed-responsive-item'],
        'layout' => Html::tag('p', '{iframe}', ['class' => 'embed-responsive embed-responsive-16by9'])
    ]);
}
// If the images found in the description must be optimized.
if($optimizeImgs)
{
    $content = \yiingine\widgets\OptimizeImgs::widget([
        'html' => $content,
        'options' => ['class' => 'embed-responsive-item', 'style'=>'object-fit:cover;'],
        'unwrapParagraph' => true,
        'layout' => Html::tag('p', '{img}', ['class' => 'embed-responsive embed-responsive-4by3']),
        'addZoomOverlay' => true
    ]);
}

// If the images or iframes found in the content must be lazy loaded.
if($lazyLoad)
{
    // Use LazyLoad widget.
    $content = \yiingine\widgets\LazyLoad::widget([
        'html' => $content,
    ]);
}
echo $content;
