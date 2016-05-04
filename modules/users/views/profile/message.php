<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

// Reuse the message view from the registration controller.
echo $this->render('/register/message', [
    'model' => $model, 
    'message' => $message, 
    'type' => $type, 
    'title' => Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Profile')
]);
