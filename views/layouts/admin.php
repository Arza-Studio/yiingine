<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use \yii\helpers\Url;
use \yii\web\View;
use \yiingine\models\MenuItem;
use \yiingine\models\admin\AdminParameters;
use rmrevin\yii\fontawesome\FA;

$this->registerAssetBundle('yiingine\assets\admin\AdminAsset');

// "Application name | Catchphrase" or "Page title | Catchphrase | Application name"
$this->title .= ($this->title ? ' | ': '').' Admin | '.Yii::$app->name;

$this->beginContent('@yiingine/views/layouts/main.php');

?>
    <div id="header" class="navbar navbar-fixed-top">
        <div id="flash-messages">
            <?php
            
            //Yii::$app->session->addFlash(\yiingine\widgets\FlashMessage::WARNING, 'Warning');
            //Yii::$app->session->addFlash(\yiingine\widgets\FlashMessage::DANGER, ['message' => 'Danger', 'slideUp' => false]);
            //Yii::$app->session->addFlash(\yiingine\widgets\FlashMessage::SUCCESS, 'Success');
            
            echo \yiingine\widgets\FlashMessage::display();
            
            // Sets the correct body padding when the height of the navbar changes.
            $this->registerJs('$(window).on("resize", function() {$("body, #navigation").css("padding-top", $("#header").height());});');
            $this->registerJs('$("body, #navigation").css("padding-top", $("#header").height());');
            ?>
        </div>
        <div class="navbar-header pull-left">
            <a href="<?= Url::to(['/']); ?>" title="<?= Yii::t(\yiingine\web\admin\Controller::className(), 'Go to website home page'); ?>" class="btn"><?php echo Yii::$app->name; ?></a>
        </div>
        <div id="actionTitle" class="hidden-xs">
            <?php
            echo \yii\widgets\Breadcrumbs::widget([
                'tag' => 'ol',
                'options' => ['class' => 'breadcrumb'],
                'itemTemplate' => '<li>{link}</li>',
                'activeItemTemplate' => '<li class="active">{link}</li>',
                'homeLink' => false, // Home link is included with links so it's always diplayed.
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs']: [
                    'label' => 'Administration',
                    'url' => ['/admin'],
                    'rel' => 'home'
                ],
            ]);
            ?>
        </div>
        <div class="navbar-header pull-right">
            <?= // Login box
            $this->renderDynamic('return \yiingine\modules\users\widgets\LoginBox::widget([
                "registerUrl" => ["/users/register"],
                "forgotPasswordUrl" => ["/users/profile/recovery"],
                "switchType"=> \yiingine\modules\users\widgets\LoginBox::DROPDOWN,
                "returnLogoutUrl" => \yii\helpers\Url::to(["/"])
            ]);'); // renderDynamic() is used because the login box varies depending on the logged in user.
            ?>
            <?= // Language box
            \yiingine\widgets\LangBox::widget([
                'displayMode' => \yiingine\widgets\LangBox::CODE,
                'switchType' => \yiingine\widgets\LangBox::DROPDOWN
            ]);
            ?>
            <?php // Toggle navigation ?>
            <button type="button" class="navbar-toggle collapsed btn" data-toggle="collapse" data-target="#adminMenu" aria-expanded="false" aria-controls="navbar">
                <?= FA::icon('navicon'); ?>
            </button>
        </div>
    </div>

    <nav id="navigation" class="col-sm-3 col-md-2">
        <?php # Admin menu
        echo \yiingine\widgets\DBMenu::widget([
            'id' => 'adminMenu',
            'menuName' => 'adminMenu',
            'menuOptions' => ['class' => 'list-group panel  navbar-collapse collapse'],
            'listTag' => 'div',
            'listOptions' => ['class' => 'collapse list-group-submenu'],
            'listBeginRendering' => function($options, $depth)
            {
                echo Html::beginTag('div', $options);echo "\n";
                echo Html::beginTag('div', ['class' => 'panel']);echo "\n"; // https://github.com/twbs/bootstrap/issues/10966
            },
            'listEndRendering' => function($options, $depth)
            {
                echo Html::endTag('div');echo "\n";
                if($depth !== 0) echo Html::endTag('div');echo "\n";
            },
            'listItemTagDisplay' => false,
            'menuItemOptions' => ['class' => 'list-group-item'],
            'menuItemRendering' => function($text, $url, $options, $depth, $current, $index)
            {
                $options['data-parent'] = ($depth === 0) ? '#navigation' : '.parent'.$current->parent->id.' + .list-group-submenu';
                // If this current menu item has a child.
                if($current->displayedMenuItems)
                {
                    $options['class'] .= ' parent'.$current->id;
                    $options['data-toggle'] = 'collapse';
                    $options['data-target'] = '#navigation .depth'.$depth.'.menuItem'.($index+1).' + .list-group-submenu';
                    // Add an arrow after the text.
                    $text .= FA::icon('caret-down');
                    $url = '#';
                }
                echo Html::a($text, $url, $options); // Echo a link tag.
            },
            'lockMenuItemsCallback' => 'initializeAdminMenu();' // see : main.js
        ]);
        ?>
        <div class="adminInfos hidden-xs">
            <span id="yiingineVersion" class="label">Yiingine v<?php echo YIINGINE_VERSION; ?></span>
            <span id="adminDisplayMode" class="label"><?php echo ucfirst(Yii::t(\yiingine\web\admin\Controller::className(), '{mode} mode', ['mode' => AdminParameters::getDisplayModeLabel($this->context->adminDisplayMode)])); ?></span>
        </div>
    </nav>

    <?php # Content ?>
    <div class="container-fluid">
        <div class="row">
            <div id="content" class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2">
                <?php echo $content; ?>
            </div>
            <?php if(isset($this->params['leftButtons']) || isset($this->params['centerButtons']) || isset($this->params['rightButtons'])): ?>
                <div id="actions" class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-4">
                                <?php if(isset($this->params['leftButtons'])):?>
                                    <?php foreach($this->params['leftButtons'] as $item){ echo $item; }; ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-xs-4" style="text-align:center;">
                                <?php if(isset($this->params['centerButtons'])):?>
                                    <?php foreach($this->params['centerButtons'] as $item){ echo $item; }; ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-xs-4" style="text-align:right;">
                                <?php if(isset($this->params['rightButtons'])):?>
                                    <?php foreach($this->params['rightButtons'] as $item){ echo $item; }; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>


<?php $this->endContent(); ?>
