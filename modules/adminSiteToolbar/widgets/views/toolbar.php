<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$widget = $this->context;

# Css
$p = Yii::$app->adminPalette;
$this->registerCss('
.adminSiteToolbar { background-color: '.$p->get('Gray', -70).'; border-color: '.$p->get('Gray', 30).'; }
body .adminSiteToolbarBtn { background-color: '.$p->get('Gray', -70).' !important; color: '.$p->get('Gray', 30).' !important; border-color: '.$p->get('Gray', 30).' !important; }
body .adminSiteToolbarBtn:hover { background-color: '.$p->get('Gray', -55).' !important; color: '.$p->get('Gray', 50).' !important; border-color: '.$p->get('Gray', 50).' !important; }
body .adminSiteToolbarBtn.active { background-color: '.$p->get('AdminDefault', -70).' !important; color: '.$p->get('AdminDefault').' !important; border-color: '.$p->get('AdminDefault').' !important; }
', ['media' => 'screen']);

# Javascript
$id = $widget->id;
$onToolbarShow = addslashes($widget->onToolbarShow);
$onToolbarShown = addslashes($widget->onToolbarShown);
$onToolbarHide = addslashes($widget->onToolbarHide);
$onToolbarHidden = addslashes($widget->onToolbarHidden);
$activeFirstButton = \yiingine\libs\Functions::strbool($widget->activeFirstButton);
$this->registerJs('var '.$id.';', \yii\web\View::POS_HEAD);
$this->registerJs(<<<JS
$id = new Toolbar('$id', {
    activeFirstButton : $activeFirstButton,
    onToolbarShow: '$onToolbarShow',
    onToolbarShown: '$onToolbarShown',
    onToolbarHide: '$onToolbarHide',
    onToolbarHidden: '$onToolbarHidden'
});
$(window).on('load', function(){
    $id.init();
});
JS
,\yii\web\View::POS_END);

# Html
?>
<nav id="<?= $this->context->id; ?>" class="navmenu navmenu-default navmenu-fixed-left adminSiteToolbar">
    <ul class="nav navmenu-nav">
        <?php foreach($buttons as $button): ?>
            <li><?= $button; ?></li>
        <?php endforeach; ?>
    </ul>
    <button id="<?= $this->context->id; ?>-hide-btn" class="btn adminSiteToolbarBtn adminSiteToolbarHideBtn" type="button"><?= rmrevin\yii\fontawesome\FA::icon('close'); ?></button>
</nav>
<button id="<?= $this->context->id; ?>-show-btn" class="btn adminSiteToolbarBtn adminSiteToolbarShowBtn" type="button"><?= rmrevin\yii\fontawesome\FA::icon('pencil'); ?></button>
