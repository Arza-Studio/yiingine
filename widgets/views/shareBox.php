<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use \yii\web\View;
use \yiingine\widgets\ShareBox;
use rmrevin\yii\fontawesome\FA;
use webulla\sharelinks\ShareLinks;

# Html
$shareLinks = ShareLinks::widget([
    'url' => $url,
    'title' => $title,
    'body' => $description,
    'links' => $links
]);

switch($type)
{
    case ShareBox::BUTTONS:
        echo $shareLinks;
    break;
    case ShareBox::POPOVER:
        echo Html::button(FA::icon('share-alt'), [
            'id' => $this->context->id,
            'class' => 'btn btn-primary shareBoxPopover',
            'title' => Yii::t(get_class($this->context), 'Share {something}', ['something' => $title])
        ]);
        $this->registerJs('
        $("#'.$this->context->id.'").popover(
        {
            html: true,
            content: "'.addslashes($shareLinks).'",
            placement: "left"
        })
        .on("inserted.bs.popover", function()
        {
            var popover = $(this).next(".popover");
            var popoverContent = popover.find(".popover-content");
            var contentWidth = parseInt(popoverContent.css("padding-left")) + parseInt(popoverContent.css("padding-right"));
            var contentHeight = parseInt(popoverContent.css("padding-top")) + parseInt(popoverContent.css("padding-bottom"));
            popover.find(".share .btn").each(function(index){
                var btn = $(this);
                contentWidth += btn.outerWidth(true);
                if(index==0) contentHeight += $(this).outerHeight(true);
                btn.sharelinks();
            });
            popoverContent.css({width:contentWidth, height:contentHeight});
        });', View::POS_READY);
    break;
    default:
        throw new \yii\base\InvalidParamException('Invalid type.');
}
        
