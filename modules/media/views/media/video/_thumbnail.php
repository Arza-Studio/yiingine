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
    $layout = '<div class="container-fluid"><div class="row-eq-height"><div class="col-sm-4">{iframe}</div><div class="col-sm-8">{header}{description}<div class="labels">{date}{duration}{language}</div>{footer}</div></div></div>';
}
else
{
    $layout = '{iframe}{header}<div class="labels">{date}{duration}{language}</div>{description}{footer}';
}
if(!$model->video_title)
{
    $layout = str_replace('{header}', '', $layout);
}

# Iframe
$iframe = $this->render('_iframe', ['model' => $model, 'lazyLoad' => $this->context->imageLazyLoad]);
$iframeHtml = Html::tag('div', $iframe, ['class' => 'iframe']);

# Date
$dateHtml = '';
if($model->video_date && $model->video_date != '0000-00-00')
{
    $dateHtml = Html::tag('p', ucwords(Yii::$app->formatter->asDate($model->video_date, "MMMM yyyy")), ['class' => 'date label']);
}

# Duration
$durationHtml = $model->video_duration ? Html::tag('p', $model->video_duration, ['class' => 'duration label']) : '';

# Language
$languageHtml = $model->video_language ? Html::tag('p', ucfirst(\locale_get_display_language($model->video_language, Yii::$app->language)), ['class' => 'language label']): '';

# Play
$playHtml = Html::button(FA::icon('play'), [
    'id' => $this->context->id.'PlayBtn',
    'class' => 'btn btn-primary'
]);
if($src = \keltstr\simplehtmldom\SimpleHTMLDom::str_get_html($model->video_iframe)->find('iframe', 0)->getAttribute('src'))
{ 
    $src = str_replace('https://www.youtube.com/embed/','http://www.youtube.com/watch?v=', $src);
    \roman444uk\magnificPopup\MagnificPopup::widget([
        'target' => '#'.$this->context->id.'PlayBtn',
        'options' => [
            'items' => [[
                'src' => $src,
                'type' => 'iframe',
            ]],
        ]
    ]);
}

$this->context->layout = $layout;
$this->context->layoutItems = [
    '{iframe}' => $iframeHtml,
    '{date}' => $dateHtml,
    '{duration}' => $durationHtml,
    '{language}' => $languageHtml,
];
$this->context->footerLayout = '{play}{share}{url}';
$this->context->footerLayoutItems = ['{play}' => $playHtml];

echo $this->context->view->render('@yiingine/modules/media/widgets/views/_thumbnail.php');
