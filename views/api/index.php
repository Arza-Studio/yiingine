<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

echo Yii::t(\yiingine\controllers\ApiController::className(), 'Welcome to the {app} API', ['app' => Yii::$app->name]).".\n";

if(Yii::$app->user->isGuest)
{
    echo Yii::t(\yiingine\controllers\ApiController::className(), 'You are not logged in.')."\n";
}
else
{
    echo Yii::t(\yiingine\controllers\ApiController::className(), 'Your are logged in as {user}.', ['user' => Yii::$app->user->getIdentity()->username])."\n";
}
