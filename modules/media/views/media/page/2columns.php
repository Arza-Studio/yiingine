<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;

// /**
//  * AssetBundle for the Page medium.
//  * */
// class PageAsset extends \yii\web\AssetBundle
// {
//     /** 
//      * @inheritdoc 
//      * */
//     public $sourcePath = '@app/modules/media/assets/media/page';
    
//     /** 
//      * @inheritdoc 
//      * */
//     public $css = ['default.css'];
// }

# View options
/** @var string ordering html items. */
if(!isset($layout)) $layout = '<div class="container"><div class="row"><div class="col-sm-8 corpus">{header}{content}</div><div class="col-sm-4">{aside}</div></div></div>';
/** @var string the tag used for the heading (title) of the page. */
if(!isset($headingTag)) $headingTag = 'h1';
/** @var array the attribute of the article tag. */
if(!isset($options)) $options = [];

# Data
/** @var integer the model id. */
$id = $model->id;
/** @var string the model title. */
$title = $model->getTitle();

# Title
$this->title = $title;

# Background
if($model->background)
{
    Yii::$app->params['background'] = $model->getManager('background')->getFileUrl($model);
}

# Breadcrumbs
if(isset($this->params['breadcrumbs']))
{
    array_unshift($this->params['breadcrumbs'], $this->title);
}
else
{
    $this->params['breadcrumbs'][] = $this->title;
}

// PageAsset::register($this);

$strings = [];

# Admin Overlay
\yiingine\widgets\admin\AdminOverlay::widget([
    'selector' => '.default[data-id="'.$id.'"] .container',
    'model' => $model
]);

# Html
// Article tag opening
echo Html::beginTag('article', array_merge_recursive([
    'class' => 'default',
    'data-type' => 'page',
    'data-id' => $id,
], $options));

// {header}
if(strpos($layout, '{header}') !== false)
{
    // Heading tag with title class.
    $headerHtml = Html::tag($headingTag, $title, ['class' => 'page-header']);
    // Wrapping into a header tag.
    $headerHtml = Html::tag('header', $headerHtml);
    // Remplacement in the layout.
    $strings['{header}'] = $headerHtml;
}

// {content}
if(strpos($layout, '{content}') !== false)
{
    // Add content through _content view
    $contentHtml = $this->render('@app/modules/media/views/media/page/_content.php', [
        'model' => $model, 
        'variables' => $variables
    ]);
    // Remplacement in the layout.
    $strings['{content}'] = $contentHtml;
}

// {aside}
if(strpos($layout, '{aside}') !== false)
{
    $asideHtml = '';
    if(isset($model->associated_media)) //If there are associated media.
    {
        // Administrators are allowed to see all associated media.
        $relation = !Yii::$app->user->isGuest && !Yii::$app->user->getIdentity()->superuser ? 'all_associated_media' : 'associated_media';
        foreach($model->$relation as $child)
        {
            $asideHtml .= \yiingine\modules\media\widgets\Thumbnail::widget(['model' => $child]);
        }
        // Wrapping into a aside tag.
        $asideHtml = Html::tag('aside', $asideHtml);
    }
    // Remplacement in the layout.
    $strings['{aside}'] = $asideHtml;
}

// Add contructed layout.
echo strtr($layout, $strings);

// Article tag closing
echo Html::endTag('article');
