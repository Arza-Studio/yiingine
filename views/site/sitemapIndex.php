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
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
   <?php foreach($maps as $map):?>
   <sitemap>
      <loc><?php echo $map['url'] ?></loc>
      <?php if(isset($map['lastmod'])): ?>
          <lastmod><?php echo $map['lastmod'] ?></lastmod>
      <?php endif; ?>
   </sitemap>
   <?php endforeach; ?>
</sitemapindex>
