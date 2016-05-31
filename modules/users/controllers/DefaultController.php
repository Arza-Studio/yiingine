<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\modules\users\controllers;

use \Yii;
use \yiingine\modules\users\models\User;
use \yii\helpers\Url;

/** Default controller for the users module. */
class DefaultController extends \yiingine\web\SiteController
{
    /**
    * Logout the current user and redirect to returnLogoutUrl.
    */
    public function actionLogout()
    {
        if(Yii::$app->user->isGuest) //If user is not logged in.
        {
            throw new \yii\web\UnauthorizedHttpException();
        }
        
        if(Yii::$app->request->post('LogoutForm'))
        {                    
            Yii::$app->user->logout();
            
            $url = isset($_POST['LogoutForm']['returnUrl']) ? 
                $_POST['LogoutForm']['returnUrl'] : 
                \yii\helpers\Url::to($this->module->returnLogoutUrl);
            
            /* Will be intercepted by the User component to confirm the logging out of the user.
             * A normal flash message cannot be used here because the session is destroyed.*/
            $url .= parse_url($url, PHP_URL_QUERY) ? '&confirmLogout=1': '?confirmLogout=1';
            
            //Redirect the the user to his previous url if it was specified.
            return $this->redirect($url);
        }
    
        throw new \yii\web\MethodNotAllowedHttpException(405);
    }

    /**
    * Displays a login page.
    */
    public function actionLogin()
    {
        if(!Yii::$app->user->isGuest) // If user is already logged in.
        {            
            if(!$this->module->allowProfileEdition) // If profile edition is disabled.
            {
                return $this->goHome();
            }
                
            // Redirect the user to the URL he originally requested or redirect to the profile page if none.
            return $this->redirect(Yii::$app->user->getReturnUrl(Url::to(['/users/profile/index'])));
        }
        
        // If the site is read only or accounts have been disabled.
        if(Yii::$app->getParameter('app.read_only') || Yii::$app->getParameter('yiingine.users.disable_user_accounts'))
        {
            throw new \yii\web\HttpException(503); // Service unavailable.
        }
        
        $model = new \yiingine\modules\users\models\UserLogin();
        
        if($model->load(Yii::$app->request->post())) // Collect user input data.
        {
            if($model->login()) // Validate and log in user and redirect to previous page if valid.
            {
                if(!$this->module->allowProfileEdition) // If profile edition is disabled.
                {
                    return $this->goHome();
                }
                
                // Redirect the user to the URL he originally requested or redirect to the profile page if none.
                return $this->redirect(Yii::$app->user->getReturnUrl(Url::to(['/users/profile/index'])));
            }
        }
        
        return $this->render('login', ['model' => $model]);
    }
    
    /**
    * Displays the login page for the administration side.
    */
    public function actionAdminLogin()
    { 
        if(!Yii::$app->user->isGuest) // If user is already logged in.
        {
            if(Yii::$app->user->getIdentity()->superuser) // If user is a super user.
            {
                return $this->redirect(Url::to(['/admin/default/index']));
            }
            
            throw new \yii\web\ForbiddenHttpException(); // Other users cannot access the admin.
        }
        
        $model = new \yiingine\modules\users\models\UserLogin();
        
        if($model->load(Yii::$app->request->post())) // Collect user input data.
        {
            if($model->login()) // Validate and log in user and redirect to previous page if valid.
            {                
                // Remove all flashes so messages from the site do not get displayed on the admin.
                Yii::$app->session->removeAllFlashes();
                
                // Redirect the user to the URL he originally requested or to the admin home if none.
                return $this->redirect(Yii::$app->user->getReturnUrl(Url::to(['/admin/default/index'])));
            }
        }
        
        return $this->render('adminLogin', ['model' => $model]);
    }
    
    /**
     * Return the module sitemap.
     */
    public function actionModuleMap()
    {    
        $pages = [];
        
        // If registration is enabled, map the page.
        if($this->module->allowRegistration)
        {
            $pages[] = [
                'loc' => ['/users/register'],
                'changefreq' => 'monthly',
                'priority' => 0.9
            ];
        }
        
        // If password recovery is enabled, map the page.
        if($this->module->allowPasswordRecovery)
        {
            $pages[] = [
                'loc' => ['/users/profile/recover'],
                'changefreq' => 'yearly', // Never should only be used for archived urls.
                'priority' => 0 // Not an important page.
            ];
        }
        
        // If public profiles are enabled, map every single one.
        if($this->module->allowPublicProfiles)
        {
            // If the user pages are not in cache.
            if(!$userPages = Yii::$app->cache->get('UsersModule.allUserPagesForModuleMap'))
            {
                $userPages = [];
                
                // For each active user.
                foreach(User::find()->where(['status' => [User::STATUS_ACTIVE, User::STATUS_BANNED]])->all() as $model)
                {
                    $userPages[] = [
                        'loc' => ['/users/profile/index', 'id' => $model->id],
                        'changefreq' => 'monthly',
                        'lastmod' => (new \DateTime($model->ts_updt))->format(\DateTime::W3C),
                        'priority' => 0.1 // User pages are not that important.
                    ];
                }
                
                // Cache the user pages.
                Yii::$app->cache->set('UsersModule.allUserPagesForModuleMap', $userPages, 0, new \yiingine\caching\GroupCacheDependency([User::className(), \yiingine\modules\customFields\models\CustomField::className()]));
            }
            
            $pages = array_merge($pages, $userPages);
        }
        
        Yii::$app->response->content = $this->renderPartial('//site/sitemap', ['pages' => $pages]);
    }
}
