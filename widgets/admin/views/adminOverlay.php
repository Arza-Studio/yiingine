<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \Yii;
use \yii\helpers\Html;

$this->registerCss('
.adminOverlay { border-color:'.Yii::$app->adminPalette->get('AdminDefault').'; }
.adminOverlay.disabled { border-color:'.Yii::$app->adminPalette->get('AdminWarning').'; }
.adminOverlayTitle { background-color:'.Yii::$app->adminPalette->get('AdminDefault').'; }
.adminOverlay.disabled .adminOverlayTitle { background-color:'.Yii::$app->adminPalette->get('AdminWarning').'; }
');

$this->registerJs('var '.$this->id.' = new adminOverlay("'.$this->id.'");', \yii\web\View::POS_END);
// Hide adminOverlays.
$this->registerJs('$(".hidden.adminOverlay").parent().hide();', \yii\web\View::POS_READY);

$options = $this->context->options;
$model = $this->context->model;
$title = $this->title;

$this->options['id'] = 'adminOverlay'.$this->id;

// Set HTML classes depending on what is being overlayed.
$classesToAdd = 'adminOverlay noAjax';
if($model)
{
    $classesToAdd .= ' '.($model->getEnabled() ? '' : 'disabled').' '.($model->getEnabled() || $this->context->forceDisplay ? '': 'hidden');
}
if(!isset($options['class']))
{
     $options['class'] = $classesToAdd;
}
else
{
    $options['class'] .= ' '.$classesToAdd;
}

// Set the title of the overlay.
$options['title'] = Yii::t('generic', 'Modify').' - ';
$options['title'] .= $title ? $title : $model instanceof \yiingine\db\ModelInterface ? $model->getModelLabel(): $model->formName(); 

// Url
$url = $this->url? $this->url : $model->getAdminUrl();
// Save the current url so it can be redirected to from the admin.
$url.= (strpos($url, '&') !== false ? '&': '?').'returnUrl='.urlencode(Yii::$app->request->requestUri);
$options['onclick'] = 'window.location.href="'.$url.'";';

echo Html::beginTag('div', $options);
echo $content;
echo Html::endTag('div');
