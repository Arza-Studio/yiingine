<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$appName = Yii::$app->name;

$platform = Yii::$app->params['platform'];

$assets = \yiingine\assets\common\CommonAsset::register($this);

$imagePath = $assets->baseUrl.'/images/updateBrowser/';

$this->beginPage();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
          
    <head>
        <title><?php echo mb_strtoupper($appName).' | '.Yii::t(\yiingine\controllers\SiteController::className(), 'ERROR: BROWSER OUT-OF-DATE'); ?></title>
        <meta http-equiv="cache-control" content="no-cache" />
        <meta charset="utf-8" />
        <meta name="robots" content="noindex, nofollow" />
    </head>
    <body style="background:#ddd;color:#222;font-size:13px;font-family:Arial, Helvetica, sans-serif;text-align:center;">
    <?php $this->beginBody(); ?>
        <div style="position:absolute;top:50%;left:50%;width:550px;height:320px;margin:-145px 0px 0px -275px;border:1px solid #333;background:#ccc;padding:20px;">
        <?php # H1 TITLE ?>
        <h1 style="font-size:20px;"><?php echo Yii::t(\yiingine\controllers\SiteController::className(), 'YOUR BROWSER IS OUT-OF-DATE'); ?></h1>

        
        <p><?php echo Yii::t(\yiingine\controllers\SiteController::className(), 'The website <strong>{app}</strong> requires a more recent browser.', ['app' => $appName]); ?></p>
        
        <?php if($platform): ?>
            <p><?php echo Yii::t(\yiingine\controllers\SiteController::className(), 'Here is a list of recent browsers that work perfectly on <strong>{platform}</strong>.', ['platform' => ucfirst($platform)]); ?><br />
            <?php echo Yii::t(\yiingine\controllers\SiteController::className(), 'Please select one:'); ?></p>
            
            <?php 
            # FIREFOX 
            $firefox = ['windows','apple','linux'];
            if(in_array($platform, $firefox)):
            ?>
            <span><a href="http://www.mozilla.org/" target="_blank" title="Firefox"><img style="border:none;" src="<?php echo $imagePath.'firefoxLogo.png'; ?>" height="100" width="100" alt="Firefox" /></a></span>
            <?php endif; ?>
            
            <?php
            # SAFARI 
            $safari = ['windows','apple'];
            if(in_array($platform, $safari)):
            ?>
            <span><a href="http://www.apple.com/safari/" target="_blank" title="Safari"><img style="border:none;" src="<?php echo $imagePath.'safariLogo.png'; ?>" height="100" width="100" alt="Safari" /></a></span>
            <?php endif; ?>
            
            <?php 
            # CHROME
            $chrome = ['windows','apple','linux'];
            if(in_array($platform, $chrome)):
            ?>
            <span><a href="http://www.google.com/chrome" target="_blank" title="Google Chrome"><img style="border:none;" src="<?php echo $imagePath.'chromeLogo.png'; ?>" height="100" width="100" alt="Google Chrome" /></a></span>
            <?php endif; ?>
            
            <?php 
            # INTERNET EXPLORER
            $ie = ['windows'];
            if(in_array($platform, $ie)):
            ?>
            <span><a href="http://windows.microsoft.com/en-CA/internet-explorer/products/ie/home" target="_blank" title="Internet Explorer"><img style="border:none;" src="<?php echo $imagePath.'ieLogo.png'; ?>" height="100" width="100" alt="Internet Explorer" /></a></span>
            <?php endif; ?>
            
            <?php 
            # KONQUEROR
            $konqueror = ['linux'];
            if(in_array($platform, $konqueror)):
            ?>
            <span><a href="http://www.konqueror.org/download/" target="_blank" title="Konqueror"><img style="border:none;" src="<?php echo $imagePath.'konquerorLogo.png'; ?>" height="100" width="100" alt="Konqueror" /></a></span>
            <?php endif; ?>
            <?php 
            # KONQUEROR
            if(in_array($platform, ['linux', 'apple', 'windows'])):
            ?>
            <span><a href="http://download-chromium.appspot.com/" target="_blank" title="Chromium"><img style="border:none;" src="<?php echo $imagePath.'chromiumLogo.png'; ?>" height="100" width="100" alt="chromium" /></a></span>
            <?php endif; ?>
            <?php 
            # OPERA
            $opera = ['windows','apple','linux'];
            if(in_array($platform, $opera)):
            ?>
            <span><a href="http://www.opera.com/browser/" target="_blank" title="Opera"><img style="border:none;" src="<?php echo $imagePath.'operaLogo.png'; ?>" height="100" width="100" alt="Opera" /></a></span>
            <?php endif; ?>
        <?php endif; ?>
        
        <h3><?php echo Yii::t(\yiingine\controllers\SiteController::className(), 'If you have updated your browser or were taken here by mistake, click <a href="{url}">here</a> to go back to the site.', ['url' => \yii\helpers\Url::to(['/'])]); ?></h3>        
        <form method="POST" onSubmit="return confirm('<?php echo Yii::t(\yiingine\controllers\SiteController::className(), 'It is very likely that the site will malfunction. Keep going?');?>')">
            <input type="hidden" name="ignore" value="1" />
            <?php echo \yii\helpers\Html::submitButton(Yii::t(\yiingine\controllers\SiteController::className(), 'Ignore this warning')); ?>
        </form>
        </div>
        <?php $this->endBody(); ?>
    </body>
    
</html>
<?php $this->endPage();
