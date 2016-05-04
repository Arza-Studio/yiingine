<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

Yii::$app->response->format = \yii\web\Response::FORMAT_XML;
Yii::$app->response->data = null;

?>
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
   <?php           
       foreach($pages as $page)
       {
           /* If loc was given as an array, it is meant to be used
            * as an argument to Url::tol() and made multilingual.*/
           if(is_array($page['loc']))
           {
               $urls = [];
               
               //Generate a url for each available language.
               foreach(Yii::$app->params['app.available_languages'] as $lang)
               {
                   $urls[$lang] = Yii::$app->urlManager->createAbsoluteUrl($page['loc'], null, count(Yii::$app->params['app.supported_languages']) > 1 ? $lang : null);
               }
               
               foreach($urls as $url) //Generate a sitemap entry for each url.
               {
                   echo '  <url>'."\n";
                   echo '    <loc>'.$url.'</loc>'."\n";
                   //Generates a xhtml:link block to enhance support for google sitemaps (#1187).
                   foreach($urls as $lang => $url)
                   {
                       $rel = '';
                       
                       // If there are more than one supported languages.
                       if(count(Yii::$app->params['app.supported_languages']) > 1)
                       {
                           // Indicate it in the rel tag.
                           $rel = ' rel="alternate" hreflang="'.$lang.'"';
                       }
                       
                       echo '    <xhtml:link'.$rel.' href="'.$url.'" />'."\n";   
                   }
                   echo isset($page['changefreq']) ? '    <changefreq>'.$page['changefreq'].'</changefreq>'."\n": '';
                   echo isset($page['lastmod']) ? '    <lastmod>'.$page['lastmod'].'</lastmod>'."\n": '';
                   echo isset($page['priority']) ? '    <priority>'.$page['priority'].'</priority>'."\n": '';
                   echo '  </url>'."\n";
               }
           }
           else //Do not translate.
           {
               echo '  <url>'."\n";
               echo '    <loc>'.$page['loc'].'</loc>'."\n";
               echo isset($page['changefreq']) ? '    <changefreq>'.$page['changefreq'].'</changefreq>'."\n": '';
               echo isset($page['lastmod']) ? '    <lastmod>'.$page['lastmod'].'</lastmod>'."\n": '';
               echo isset($page['priority']) ? '    <priority>'.$page['priority'].'</priority>'."\n": '';
               echo '  </url>'."\n";
           }
       }
   ?>
</urlset>
