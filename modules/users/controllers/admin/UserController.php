<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\modules\users\controllers\admin;

use \Yii;

/** The admin controller for the User model.*/
class UserController extends \yiingine\web\admin\ModelController
{        
    /**
    * @inheritdoc
    */
    public function actionDelete($id)
    {
        // If the currently logged in user is trying to delete his own account.
        if(Yii::$app->user->getIdentity()->id === $id)
        {
            throw new \yii\web\ForbiddenHttpException();  
        }
        
        return parent::actionDelete($id);
    }
    
    /**
     * @inheritdoc
     * */
    public function getFormStructure($model)
    {
        return $this->requireFile('/_forms/_'.lcfirst($model->formName()), ['model' => $model], $this);
    }
}
