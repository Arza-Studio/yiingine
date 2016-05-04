<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

Yii::$app->response->statusCode = 503;
Yii::$app->response->headers->add('Retry-After', 3600);  //Advises robots to try again in 1 hour.

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>WEBSITE DOWN - SITE WEB FERMÉ - SITIO WEB CERRADO - WEBSITE GESCHLOSSEN - SITO CHIUSO</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta name="robots" content="noindex, nofollow" />
    </head>
    <body style="background:#ddd;color:#222;font-size:13px;font-family:Arial, Helvetica, sans-serif;text-align:center;">
        <div style="position:absolute;top:50%;left:50%;width:550px;height:275px;margin:-140px 0px 0px -275px;border:1px solid #333;background:#ccc;padding:20px;font-weight:bold;">
            <p style="font-size:16px;line-height:18px;;margin-bottom:15px;">This website is down for maintenance.<br />Sorry for the inconvenience.</p>
            <p style="font-size:16px;line-height:18px;;margin-bottom:15px;">Ce site est fermé pour maintenance.<br />Veuillez nous excuser pour la gêne occasionnée.</p>
            <p style="font-size:16px;line-height:18px;;margin-bottom:15px;">Este sitio web está en mantenimiento.<br /> Disculpa las molestias.</p>
            <p style="font-size:16px;line-height:18px;;margin-bottom:15px;">Diese Website ist zurzeit im Wartungsmodus.<br />Es tut uns leid.</p>
            <p style="font-size:16px;line-height:18px;;margin-bottom:15px;">Questo sito è fuori servizio per manutenzione.<br />Ci scusiamo per l'inconveniente.</p>
        </div>
    </body>
</html>
