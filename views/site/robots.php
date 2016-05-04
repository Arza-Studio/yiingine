<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
Yii::$app->response->headers->add('Content-Type', 'text/plain');

if(YII_DEBUG): //If Yii is in debug mode, block all robots.?>
User-agent: *
Disallow: /
<?php else: ?>
# robots.txt generated at http://www.mcanerin.com

User-agent: Googlebot
Disallow: 
User-agent: googlebot-image
Disallow: 
User-agent: googlebot-mobile
Disallow: 
User-agent: MSNBot
Disallow: 
User-agent: Slurp
Disallow: 
User-agent: Teoma
Disallow: 
User-agent: twiceler
Disallow: 
User-agent: Gigabot
Disallow: 
User-agent: Scrubby
Disallow: 
User-agent: Robozilla
Disallow: 
User-agent: Nutch
Disallow: 
User-agent: ia_archiver
Disallow: 
User-agent: baiduspider
Disallow: 
User-agent: naverbot
Disallow: 
User-agent: yeti
Disallow: 
User-agent: yahoo-mmcrawler
Disallow: 
User-agent: psbot
Disallow: 
User-agent: asterias
Disallow: 
User-agent: yahoo-blogs/v3.9
Disallow: 

User-agent: *
Disallow: /protected/
Disallow: /workshop/
Disallow: /assets/
Disallow: /user/temp/
Disallow: /site/problemReport/
#The previous folders should be made inacessible using a .htaccess.

Sitemap: <?php echo $this->createUrl('/sitemap.xml'); ?>
<?php endif;?>
