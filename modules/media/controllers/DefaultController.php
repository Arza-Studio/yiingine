<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\modules\media\controllers;

use \Yii;
use \yiingine\modules\media\models\Medium;

/** DefaultController for the media module. This controller manages the retrieval of mediums
 * and the module map.*/
class DefaultController extends \yiingine\modules\media\web\Controller
{    
    /**
     * Index action for DefaultController. This action retrieves medium according to a 
     * unique id. A unique Id is in the form of id-fileNameEncodedTitle. Only id is
     * extracted, the rest of the string is ignored. This allows better referencing
     * and prevention of stale links. A hierachy can be specified for the sake of making
     * SEO friendly URLs in which case only the last specified Id is used.
     * 
     * Ids can either be database ids or types for singletons.
     * @params string $id the identifier of the first medium.
     * @params string $id1 the identifier of the second medium.
     * @params string $id2 the identifier of the third medium.
     * @params string $id3 the identifier of the fourth medium.
     */
    public function actionIndex($id, $id1 = null, $id2 = null, $id3 = null)
    {
        // Finds the last id used.
        if($id3) { $id = $id3; }
        else if($id2) { $id = $id2; }
        else if($id1) { $id = $id1; }
        
        $mediaClasses = [];
        foreach($this->module->mediaClasses as $class)
        {
            $mediaClasses[substr($class, strrpos($class, '\\') + 1)] = $class;
        }        
        
        if(isset($mediaClasses[$id])) // If id refers to a class.
        {    
            $type = $mediaClasses[$id];
            
            if($type::$singleton) // Only singleton can be refered to using their types.
            {
                $attributes = ['type' => $type];
            }
            else
            {
                throw new \yii\web\NotFoundHttpException();
            }
        }
        else
        {
            $id = explode('-', $id, 2); //Get the key part of id.
            $id = (int)$id[0];
            
            if(!is_integer($id) || $id < 1) //If $id is not an integer.
            {
                throw new \yii\web\BadRequestHttpException();
            }
            
            $attributes = ['id' => $id];
        }
        
        // If that medium is not in cache.
        if(!$medium = Yii::$app->cache->get('Medium'.$id))
        {            
            if(!$medium = Medium::find()->where($attributes)->one()) // If medium was not found in database.
            {
                throw new \yii\web\NotFoundHttpException();
            }
            
            //Save it to cache.
            Yii::$app->cache->set('Medium'.$id, $medium, 0, new \yiingine\caching\GroupCacheDependency([Medium::className(), \yiingine\modules\customFields\models\CustomField::className()])); 
        }
        
        // If the current user is not allowed to view this medium.
        if(!($medium->getEnabled() || (!Yii::$app->user->isGuest && Yii::$app->user->getIdentity()->superuser && $medium->isAccessible())))
        {
            throw new \yii\web\NotFoundHttpException();
        }
        
        //If the medium belongs to another module, it should not be acessed using this controller.
        if(isset($medium->module_owner_id) && $medium->module_owner_id)
        {
            $this->redirect(['/'.$medium->module_owner_id], true, 301); // Permanent redirect.
        }
        
        // If the socialMetas components is present. 
        if(Yii::$app->has('socialMetas'))
        {
            $socialMetas = Yii::$app->get('socialMetas');
            
            // If a custom meta description for this medium is set.
            if(isset($medium->description) && ($description = $medium->description))
            {
                $socialMetas->description = $description; // Override the default description.
            }
            
            //If a custom meta keywords for this medium is set.
            if(isset($medium->keywords) && ($keywords = $medium->keywords))
            {
                $socialMetas->keywords = $keywords; // Override the default keywords.
            }
            
            if($thumbnail = $medium->getThumbnail()) // If a thumbnail is defined.
            {
                $socialMetas->thumbnail = $thumbnail; // Override the default thumbnail.
            }
        }
        
        $views = $medium->getViews();
        
        // If the medium is to be rendered by a view.
        if(is_array($views))
        {
            $result = $this->render(
                $medium->view == 'default' ? $this->module->defaultView : $medium->view,
                ['model' => $medium, 'variables' => $this->runBeforeRender($medium)]
            );
            $this->runAfterRender($medium);
            return $result;
        }
        // Else if a field is in charge of rendering the medium.
        else if(is_string($views))
        {
            $field = $views;
            
            $managers = $medium->getManagers();
            //If the field specified does not exist.
            if(!isset($managers[$field]))
            {
                throw new \yii\base\Exception('Field '.$field.' does not exist for Medium of type '.$medium->type.'.');
            }
            //Render the medium using the provided field.
            return $managers[$field]->render($medium);
        }
        // If the medium has no view.
        else if($views === false)
        {
            throw new \yii\web\NotFoundHttpException();
        }

        throw new \yii\base\Exception('Invalid view parameter for Medium of type '.$medium->type.'.');
    }
    
    /**
     * Generates the sitemap for this module.
     * */
    public function actionModuleMap()
    {
        $classes = []; // Classes that will be part of the module map.
        
        /*Iterates through all media classes to find those that should be
        * displayed on the module map.*/
        foreach($this->module->mediaClasses as $class)
        {
            // If the medium type is to be included in the sitemap and has a view.
            if($class::getViews() !== false && $class::$includeInSiteMap)
            {
                $classes[] = $class;
            }
        }

        $pages = []; // Pages part of the media module.
        
        foreach(Medium::find()->where(['type' => $classes])->all() as $medium)
        {
            if(!$medium->getEnabled()) // If the medium has been disabled.
            {
                continue; // Skip it.
            }
            
            $pages[] = [
                'loc' => ['/media/default/index/', 'id' => $medium->id],
                'lastmod' => (new \DateTime($medium->ts_updt))->format(\DateTime::W3C),
                'changefreq' => 'monthly',
            ];
        }
        
        Yii::$app->response->content = $this->renderPartial('//site/sitemap', ['pages' => $pages]);
    }
}
