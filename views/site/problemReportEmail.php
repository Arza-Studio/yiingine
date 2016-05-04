<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

// The attributes of the prolem report to send.
$attributes = ['dateTime', 'userId', 'url', 'method', 'code', 'message', 'browser', 'application', 'description', 'screenHeight', 'screenWidth', 'referrer'];
?>

<strong><?php echo Yii::t(\yiingine\controllers\SiteController::className(), '{app} Problem Report', ['app' => Yii::$app->name]); ?></strong><br />
<br />
<table border="0" cellpadding="10" cellspacing="0" style="border:1px solid #000000;">
    <?php foreach($attributes as $attribute): ?>
        <tr>
            <td style="vertical-align:top;background-color:#cccccc;" valign="top"><?php echo $model->getAttributeLabel($attribute); ?></td>
            <td style="vertical-align:top;" valign="top"><?php echo $model->$attribute; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
