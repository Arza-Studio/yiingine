<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use \yiingine\web\Controller;

$this->beginPage();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::$app->language; ?>">
    <head>
        <title><?php echo Html::encode($this->title); ?></title>
        <?php      
        $this->registerAssetBundle('yiingine\assets\common\CommonAsset');
        
        # Meta tags
        // Cross Site Request Forgery Meta Tags
        echo \yii\helpers\Html::csrfMetaTags();
        // Content types
        $this->registerMetaTag(['content' => 'text/html; charset=UTF-8', 'http-equiv' => 'Content-Type']);
        $this->registerMetaTag(['content' => 'text/css; charset=UTF-8', 'http-equiv' => 'Content-Style-Type']);
        $this->registerMetaTag(['content' => 'text/javascript; charset=UTF-8', 'http-equiv' => 'Content-Script-Type']);
        // Languages
        $this->registerMetaTag(['content' => Yii::$app->language, 'name' => 'language']);
        $this->registerMetaTag(['content' => Yii::$app->language, 'http-equiv' => 'Content-Language']);
        // Registering of alternate language links (see #1186)
        foreach(Yii::$app->params['app.available_languages'] as $language)
        {
            if($language == Yii::$app->language) // Skip the current language.
            {
                continue;
            }
            // Register a link tag to inform a user that an alternate version of the page exists in a different language.
            $this->registerLinkTag([
                'rel' => 'alternate',
                Yii::$app->request->hostInfo.Yii::$app->urlManager->createUrl(array_merge([$this->context->route], $this->context->actionParams), $language), //href
                'hreflang' => $language
            ]);
        }    
        
        # JAVACRIPT
        // This makes makes available some useful yii attributes and variables for use within javascript.
        if(!Yii::$app->request->isAjax) // Useless if the page has been requested using ajax.
        {
            // Each variable is defined within its own namespace to limit collisions.
            $this->registerJs(
                'yii.language = "'.Yii::$app->language.'";'
                .'yii.applicationName = "'.Yii::$app->params['app.name'].'";'
                .'yii.homeUrl = "'.\yii\helpers\Url::home().'";'
                .'yii.request = function(){};'
                .'yii.request.baseUrl = "'.Yii::$app->request->baseUrl.'";'
                .'yii.request.hostInfo = "'.Yii::$app->request->hostInfo.'";'
                .'yii.request.url = "'.Yii::$app->request->url.'";'
                .'function yiingine(){};'
                .'yiingine.side = "'.($this->context->getSide() ? 'admin' : 'site').'";'
            , \yii\web\View::POS_READY);
        }
        
        # Social metas
        if(Yii::$app->has('socialMetas')) // If the application has the socialMetas component.
        {
            Yii::$app->socialMetas->register(); // Register all social metas.
        }
        
        # Favicon
        if($favicon = Yii::$app->getParameter('app.favicon', false))
        {
            $this->registerLinkTag(['rel' => 'shortcut icon', 'type' => 'image/png', 'href' => Yii::$app->request->baseUrl.'/user/assets/'.$favicon]);
        }
        
        # Apple touch icon (Working also with Android)
        // Use file name : "apple-touch-icon-precomposed.png" to disable effects on bookmarking
        if($icon = Yii::$app->getParameter('app.apple_touch_icon', false))
        {
            $this->registerLinkTag(['rel' => 'apple-touch-icon', 'type' => 'image/png', 'href' => Yii::$app->request->baseUrl.'/user/assets/'.$icon]);
        }
        
        $this->head(); // Must be last!
        ?>
    </head>
    <body class="<?= $this->context->getSide()==Controller::ADMIN ? 'admin' : 'site'; ?>">
        <?php $this->beginBody(); ?>
            <?php  
            # No script
            // Check if the 'app.require_javascript' config entry is there and enabled. Default behavior is to require scripts.
            // Javascript does not work with ie6 and ie7 so just disable it.
            if(!Yii::$app->request->isAjax && Yii::$app->getParameter('app.require_javascript', true) && !in_array(Yii::$app->params['browser'], array('ie6', 'ie7'))):
            ?>
            <div id="noScript" class="flash-message flash-danger bg-danger">
                <?php echo Yii::t('site', 'Javascript is disabled. This site requires javascript to display properly.'); ?>
                <script type="text/javascript"> <?php // Is defined inline in order to be executed as fast as possible.?>
                    noScript = document.getElementById("noScript");
                    noScript.parentElement.removeChild(noScript);
                </script>
            </div>
            <?php endif; ?>
            
            <?php
            # Session monitor
            if(!Yii::$app->user->isGuest)
            {
                echo \yiingine\widgets\SessionMonitor::widget();
            }
            
            # Content
            echo $content; // Outputs the content of the child layout.        
        
            # Debug label
            // Display a label if debug mode is on or if database is not set to production.
            if(YII_DEBUG || DB_LOCATION != 'production')
            {
                /* Since fixed positionning interferes with zooming on mobile browsers,
                 * we disable it. */ 
                if(!Yii::$app->params['isMobileBrowser'])
                {      
                    // CSS
                    $this->registerCss('#debugMode { position:fixed; z-index:9999; bottom:2px; right:2px; background:yellow; color:red; font-size:11px; line-height:24px; height:25px; padding:2px 8px; font-family:Arial, Helvetica, sans-serif; }', ['media' => 'screen']);
                    // HTML
                    //echo '<div id="debugMode">'.(YII_DEBUG ? 'DEBUG MODE / ' : '').'DB:'.mb_strtoupper(DB_LOCATION).'</div>';
                }
            }

            ?>
        <?php $this->endBody(); ?>
    </body>    
</html>
<?php $this->endPage();
