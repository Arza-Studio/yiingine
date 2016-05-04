<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\modules\rbac\controllers\admin;

use \Yii;

/** 
 * An admin controller for authorization assignments. 
 * */
class AssignmentController extends \yiingine\modules\users\modules\rbac\web\admin\AuthorizationObjectController
{    
    /**
     * Returns the data model based on an identifier given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * A controller for a singleton model should override this method
     * to always provide the singleton model.
     * @param string the composed name of the assignment to be loaded.
     * @return Assigment the requested assignment.
     * @throws \yii\web\HttpException
     */
    public function loadModel($id)
    {                
        // Decompose the assigment ID into its two parts.
        
        $userId = substr($id, mb_strrpos($id, '-') + 1);
        $name = substr($id, 0, mb_strrpos($id, '-'));
        
        // If an assignment with that id was not found.
        if(!$assignment = Yii::$app->authManager->getAssignment($name, (int)$userId))
        {
            throw new \yii\web\NotFoundHttpException(); // Authorization Item was not found.
        }    
        
        $model = $this->model();
        
        return new $model($assignment);
    }
}
