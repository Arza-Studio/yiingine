<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Url;
use \yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;

# HTML
?>
<div class="modal fade" id="langBoxModal" tabindex="-1" role="dialog" aria-labelledby="langBoxModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?= FA::icon('globe').' '.Yii::t(\yiingine\widgets\LangBox::className(), 'Language'); ?></h4>
            </div>
            <div class="modal-body">
            <?php 
            foreach($options as $language => $url):
                // If this language is disabled.
                $class = (!in_array($language, Yii::$app->params['app.available_languages'])) ? 'btn-warning' : 'btn-default' ;
                // Set text in proper language (in the language of the language...)
                $text = ucfirst(\locale_get_display_language($language, $language));
                echo Html::a($text, $url, ['class' => 'btn btn-block '.$class]);
            endforeach;
            ?>
            </div>
            <div class="modal-footer">
                <?php 
                // Close button
                echo Html::button(Yii::t('generic', 'Close'), ['class'=>'btn btn-default', 'data-dismiss'=>'modal']);
                ?>
            </div>
        </div>
    </div>
</div>
