<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\modules\users\controllers;

use \Yii;
use \yiingine\modules\users\models\User;

/** 
 * Controller for user self registration and activation. 
 * */
class RegisterController extends  \yiingine\modules\media\web\Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return array_merge(parent::actions(), \yiingine\widgets\Captcha::actions());
    }
    
    /**
     * Presents the user with a registration form.
     */
    public function actionIndex()
    {
        if(!$this->module->allowRegistration) // If self registration is not enabled.
        {
            throw new \yii\web\NotFoundHttpException();
        }
        
        if(!Yii::$app->user->isGuest) //If a user is already logged in.
        {
            //Redirect instead of throwing a 403, because logged user end up on this page quite often.
            $this->redirect($this->module->returnLogoutUrl, 302); // Temporary redirect.
        }
        
        //If user registration or user accounts has been temporarily disabled or if the site is read-only.
        if(Yii::$app->getParameter('yiingine.uers.disable_user_registration') ||
            Yii::$app->getParameter('app.read_only') ||
            Yii::$app->getParameter('yiingine.users.disable_user_accounts')
        )
        {
            throw new \yii\HttpException(503); // Service unavailable.
        }
        
        // If the system_email configuration entry is not set.
        if(!isset(Yii::$app->params['app.system_email']))
        {
            throw new \yii\base\Exception('app.system_email configuration entry not set');
        }
        
        $model = new \yiingine\modules\users\models\RegistrationForm(); // User registration form.
        $model->scenario = 'registration';
        
        if($model->load(Yii::$app->request->post())) // If a registration form has been posted.
        {
            if($model->validate())
            {
                // Keep the password for logging in the user once he has registered.
                $sourcePassword = $model->password;
                
                // Activate the user if that option is set in the module.
                $model->status = $this->module->activeAfterRegister ? 
                    User::STATUS_ACTIVE: 
                    User::STATUS_NOACTIVE;
                
                if($model->save()) // If the user saved sucessfully.
                {
                    // If the user needs to activate his account.
                    if(!$this->module->activeAfterRegister)
                    {                                                                        
                        // If the user must contact an admin to get his account activated.
                        if(!$this->module->sendActivationMail)
                        {
                            // Notify the user.
                            return $this->render('message', [
                                'model' => $model,
                                'message' => Yii::t(__CLASS__, 'Thank you for your registration. Please contact an administrator to activate your account.'),
                                'type' => 'success'
                            ]);
                        }
                        else // An email is to be sent to the user detailing the procedure for account activation.
                        {
                            try
                            {
                                Yii::$app->mailer->view = Yii::$app->view;
                                $message = Yii::$app->mailer->compose('@yiingine/modules/users/views/register/activation.php', [
                                    'model' => $model,
                                    'activationUrl' => ['activate', 'email' => $model->email, 'activationKey' => $model->activation_key]
                                ]);
                                    
                                $message->setTo($model->email);
                                $message->setFrom(Yii::$app->getParameter('app.system_email', 'system@notset.com'));
                                $message->setSubject(Yii::t(__CLASS__, '{siteName} - account activation', ['siteName' => Yii::$app->name]));
                                    
                                if(!$message->send()) //If the message did not send.
                                {
                                    throw new \yii\web\ServerErrorHttpException(Yii::t(__CLASS__, 'Sending the message failed, please try again later.'));
                                }
                            }
                            catch(\Exception $e)
                            {
                                $model->delete(); // Activation failed so delete the user.
                                    
                                throw $e;
                            }
                            
                            /* The user account has been registered sucessfully,
                             inform the user of the next step. */
                            
                            // Notify the user.
                            return $this->render('message', [
                                'model' => $model,
                                'message' => Yii::t(__CLASS__, 'Thank you for your registration. An activation e-mail was sent to the address provided.'),
                                'type' => 'success'
                            ]);
                        }
                    }
                    else // Else the user is logged in after registration.
                    {
                        // Log the user in.
                        $login = new \yiingine\modules\users\models\UserLogin();
                        $login->password = $sourcePassword;
                        $login->username = $model->username;
                        $login->login();
                        
                        // Notify the user his registration was successful..
                        return $this->render('message', [
                            'model' => $model,
                            'message' => Yii::t(__CLASS__, 'Thank you for registering. You are now logged in.'),
                            'type' => 'success'
                        ]);
                    }
                }
            } 
        }
        
        return $this->render('index', ['model' => $model]);
    }
    
    /**
    * Activate a user account.
    * @param string $email the email of the account that needs activation.
    * @param string $activationKey the key for activating the account.
    */
    public function actionActivate($email, $activationKey)
    {
        if($this->module->activeAfterRegister) // If accounts do not need to be activated.
        {
            throw new \yii\web\NotFoundHttpException(); // Hide this action.
        }
        
        if(!Yii::$app->user->isGuest) // Only guest can activate accounts.
        {
            // Redirect instead of throwing a 403, because logged user end up on this page quite often.
            $this->redirect($this->module->returnLogoutUrl, 302); // Temporary redirect.
        }
        
        $model= User::find()->where(['email' => $email])->one();
        
        if($model && (int)$model->status === User::STATUS_ACTIVE) // If the account is already active.
        {
            throw new \yii\web\ForbiddenHttpException(Yii::t(__CLASS__, 'This account is already active.'));
        }
        else if($model->activation_key && $model->activation_key == $activationKey) // The activation data checked out.
        {
            $model->scenario = 'activation';
            $model->status = User::STATUS_ACTIVE;
            $model->save();
            
            // Notify the user that activation was sucessful.
            return $this->render('message', [
                'model' => $model,
                'message' => Yii::t(__CLASS__, 'Your account is now active. Please log in to start using the service.'),
                'type' => 'success'
            ]);
        }
        else if($model && (int)$model->status !== User::STATUS_ACTIVE && ($model->activation_key == $activationKey)) // The account has been disabled.
        {
            throw new \yii\web\ForbiddenHttpException(Yii::t(__CLASS__, 'This account is not active.'));
        }
        
        // The activation url was wrong.
        throw new \yii\web\ForbiddenHttpException(Yii::t(__CLASS__, 'Incorrect activation URL.'));
    }
    
    /**
     * This action is used to test the message view and is only available in debug mode.
     * @param string $message the message to display.
     * @param string $type the type of message to display
     * */
    public function actionMessage($message, $type = 'success')
    {
        if(!YII_DEBUG) // This action is only available in debug mode.
        {
            throw new \yii\web\NotFoundHttpException();
        }
         
        return $this->render('message', [
            'message' => $message,
            'type' => $type
        ]);
    }
}
