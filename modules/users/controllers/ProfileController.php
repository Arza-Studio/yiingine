<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\modules\users\controllers;

use \Yii;
use \yiingine\modules\users\models\User;

 /** Controller for user profile management and recovery tasks.*/
class ProfileController extends \yiingine\modules\media\web\Controller
{    
    /** 
     * Displays the public view of a profile.
     * @param integer $id the id of the profile to view.
     * */
    public function actionIndex($id = 0)
    {
        if(!$this->module->allowPublicProfiles) // If public profiles are disabled.
        {
            throw new \yii\web\NotFoundHttpException();
        }
        
        // If no id has been provided.
        if($id === 0)
        {
            if(Yii::$app->user->isGuest) // If the user is not logged in.
            {
                throw new \yii\web\BadRequestHttpException(); // Argument missing.
            }
            
            // The logged in user is viewing their profile.
            $model = Yii::$app->user->getIdentity();
        }
        else if(!$model = User::findOne($id)) // If a user matching that id could not found.
        {
            throw new \yii\web\NotFoundHttpException();
        }
        
        if($model->status == User::STATUS_NOACTIVE) // If the user is not active.
        {
            throw new \yii\web\NotFoundHttpException();
        }
        
        $model->scenario = 'userView';
        
        return $this->render('index', ['model' => $model]);
    }
    
    /**
     * Allows a user to edit their profile.
     */
    public function actionEdit()
    {
        if(!$this->module->allowProfileEdition) //If profile edition is disabled.
        {
            throw new \yii\web\NotFoundHttpException();
        }
        
        if(Yii::$app->user->isGuest) //If user is not logged in.
        {
            // Redirect instead of throwing a 403, because logged user end up on this page quite often.
            $this->redirect($this->module->returnLogoutUrl, 302); // Temporary redirect.
        }
        
        $model =Yii::$app->user->getIdentity(); //Retrieves cached user.
        $model->scenario = 'userEdit'; //Set the appropriate scenario.
        
        if($model->load(Yii::$app->request->post()))
        {
            if($model->save())
            {
                //Indicate the user that saving was sucessful.
                Yii::$app->session->addFlash(\yiingine\widgets\FlashMessage::SUCCESS, Yii::t(__CLASS__, 'Changes have been saved.'));
            }
        }

        return $this->render('edit', ['model' => $model]);
    }
    
    /**
    * Allows a user to recover a forgotten password. An activation key
    * is sent to his email address which he can use to change his password.
    */
    public function actionRecover() 
    {   
        if(!$this->module->allowPasswordRecovery)
        {
            throw new \yii\web\NotFoundHttpException();
        }
        
        // If the system_email configuration entry is not set.
        if(!isset(Yii::$app->params['app.system_email']))
        {
            throw new \yii\base\Exception('app.system_email configuration entry not set!');
        }
        
        if(!Yii::$app->user->isGuest) // If a user is logged in.
        {
            // Recovery is not needed.
            // Redirect instead of throwing a 403, because logged user end up on this page quite often.
            $this->redirect($this->module->returnLogoutUrl, 302); // Temporary redirect.
        } 
        
        $model = new \yiingine\modules\users\models\UserRecoveryForm();
        
        if($model->load(Yii::$app->request->post()))
        {
            if($model->validate())
            {
                //Find the user for which we are trying to recover a password.
                $model = User::findOne($model->userId);
                
                // You cannot recuperate the special accounts passwords. Too dangerous!
                if(in_array($model->username, Yii::$app->params['app.special_users']))
                {
                    throw new \yii\web\ForbiddenHttpException(Yii::t(__CLASS__, 'This account cannot be recovered, please contact your administrator.'));
                }
                
                if((int)$model->status !== User::STATUS_ACTIVE) // Recovery of a a non active account is forbidden.
                {
                    throw new \yii\web\UnauthorizedHttpException(Yii::t(__CLASS__, 'Recovery of a non-active or banned account is forbidden.')); // Unauthorized.
                }
                
                // Reset the user's password in case this account was hijacked.
                $model->scenario = 'recovery';
                $model->password = $model->verifyPassword = Yii::$app->security->generateRandomString();
                
                if(!$model->save())
                {
                    throw new \yii\base\Exception('Something went wrong, please contact your administrator!');
                }
                
                Yii::$app->mailer->view = Yii::$app->view;
                $message = Yii::$app->mailer->compose('@yiingine/modules/users/views/profile/recoveryEmail.php', [
                    'model' => $model,
                    'recoveryUrl' => ['reset', 'email' => $model->email, 'activationKey' => $model->activation_key]
                ]);
                            
                $message->setTo($model->email);
                $message->setFrom(Yii::$app->getParameter('app.system_email', 'system@notset.com'));
                $message->setSubject(Yii::t(__CLASS__, '{siteName} - Password recovery', ['siteName' => Yii::$app->name]));
                            
                if(!$message->send()) // If the message did not send.
                {
                    throw new \yii\web\ServerErrorHttpException(Yii::t(__CLASS__, 'Sending the message failed, please try again later.'));
                }
                
                // Notify the user.
                return $this->render('message', [
                    'model' => $model,
                    'message' => Yii::t(__CLASS__, 'A recovery e-mail was sent to your account\'s address.'),
                    'type' => 'success'
                ]);
            }
        }
        
        return $this->render('recover', ['model' => $model]);
    }
    
    /** 
     * This action allows a user to reset his password.
     * @param string $email the email of the account.
     * @param string activationKey the activation key used for resetting the password.
     * */
    public function actionReset($email, $activationKey)
    {
        if(!$this->module->allowPasswordRecovery) //If password recovery is disabled.
        {
            throw new \yii\web\NotFoundHttpException();
        }
        
        if(!Yii::$app->user->isGuest) // If a user is logged in.
        {
            // Recovery is not needed.
            // Redirect instead of throwing a 403, because logged user end up on this page quite often.
            $this->redirect($this->module->returnLogoutUrl, 302); // Temporary redirect.
        } 
        
        // Find the user with the given e-mail.
        $model = User::find()->where(['email' => $email])->one();
        
        // If the user exists and activationKey matches.
        if($model && $model->activation_key == $activationKey) 
        {
            $model->scenario = 'recovery';
        
            // Blank both passwords so the hash or the password length is not displayed.
            $model->verifyPassword = $model->password = '';
            
            if($model->load(Yii::$app->request->post()))
            {
                if($model->save()) // Save the new password.
                {                    
                    return $this->render('message', [
                        'model' => $model,
                        'message' => Yii::t(__CLASS__, 'Your password was reset. Please login to start using the service again.'),
                        'type' => 'success'
                    ]);
                }
            }
        } 
        else 
        {
            throw new \yii\web\NotFoundHttpException(Yii::t(__CLASS__, 'Invalid recovery link.'));
        }
        
        return $this->render('reset', ['model' => $model]);
    }
    
    /** 
     * This action allows a user to delete their own account.
     * */
    public function actionDelete()
    {
        if(!$this->module->allowAccountDeletion) // If users are not allowed to deleted their own accounts.
        {
            throw new \yii\web\NotFoundHttpException();
        }
        
        if(Yii::$app->user->isGuest)
        {
            throw new \yii\web\ForbiddenHttpException(); // Only logged in users can delete their accounts.
        }
        
        $model = Yii::$app->user->getIdentity();
        
        // Superusers cannot delete their accounts.
        if($model->superuser)
        {
            throw new \yii\web\ForbiddenHttpException('Superusers can only delete their accounts through the administration interface.');
        }
        
        // Retreive or generate a unique key to prevent the same POST request to be repeated.
        $deleteKey = Yii::$app->session->get('deleteKey') ? 
            Yii::$app->session['deleteKey'] : 
            Yii::$app->session['deleteKey'] = uniqid();
        
        // If the user confirmed the deletion of his account.
        if(Yii::$app->request->post('deleteKey') == $deleteKey)
        {
            Yii::$app->session->remove('deleteKey');
            
            if($model->delete())
            {
                Yii::$app->user->logout();
                
                return $this->render('message', [
                    'model' => $model,
                    'message' => Yii::t(__CLASS__, 'Your account has been deleted.'),
                    'type' => 'success'
                ]);
            }
            else // Something went wrong.
            {
                // Indicate the user that deleting failed.
                return $this->render('message', [
                    'model' => $model,
                    'message' => Yii::t(__CLASS__, 'Your account could not be deleted, please try again later or contact an administrator.'),
                    'type' => 'error'
                ]);
            }
        }
        
        return $this->render('delete', ['model' => $model, 'deleteKey' => $deleteKey]);
    }
}
