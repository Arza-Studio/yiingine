<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use \yiingine\widgets\FlashMessage;

$widget = $this->context;

if($widget->slideUp !== false)
{
    $this->registerJs('setTimeout(function(){$("#'.$widget->id.'").find(".flash-message-toggle-btn").trigger("click"); }, '.($widget->slideUp + (FlashMessage::$instancesToSlideUp * 200)).')');
}

echo Html::beginTag('div', array_merge($widget->options, [
    'class' => (isset($widget->options['class']) ? $widget->options['class']: '').' flash-message alert '.$widget->type, 
    'style' => (isset($widget->options['style']) ? $widget->options['style'] : ''),
    'id' => $widget->id
]));
?>
    <div class="message"><?= $widget->message; ?></div>
    <div class="buttons">
        <?php
        echo strtr($widget->template, array_merge([
            '{toggle}' => Html::tag('span', '', [
                'title' => Yii::t('generic', 'Show'),
                'class' => 'flash-message-toggle-btn fa fa-chevron-up',
                'onclick' => '$(this).toggleClass("fa-chevron-up").toggleClass("fa-chevron-down").parents(".flash-message").toggleClass("slidedUp").find(".message").slideToggle(200); if(typeof flashMessageToogleOnClick == "function"){ flashMessageToogleOnClick(this); }',
            ]),
            '{close}' => Html::tag('span', '', [
                'title' => Yii::t('generic', 'Close'),
                'class' => 'flash-message-close-btn fa fa-close',
                'onclick' => '$(this).parents(".flash-message").addClass("closed"); if(typeof flashMessageCloseOnClick == "function"){ flashMessageCloseOnClick(this); }',
               ])
        ], $widget->buttons));
        ?>
    </div>
</div>
