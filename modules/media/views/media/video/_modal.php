<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;

$model = $this->context->model;

# Layout
if($model->video_title)
{
    $layout = '{header}{iframe}<div class="labels">{date}{duration}{language}</div>{content}{footer}';
}
else
{
    $layout = '{iframe}<div class="labels">{date}{duration}{language}</div>{content}{footer}';
}

# Iframe
$iframe = $this->render('_iframe', ['model' => $model]);
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


$this->context->layout = $layout;
$this->context->layoutItems = [
    '{iframe}' => $iframeHtml,
    '{date}' => $dateHtml,
    '{duration}' => $durationHtml,
    '{language}' => $languageHtml,
];

echo $this->context->view->render('@yiingine/modules/media/widgets/views/_modal.php');
