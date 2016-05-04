<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\web;

use \Yii;

/**
 * This class describes a generic controller for the front-end portion of 
 * the yiin. 
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
abstract class SiteController extends Controller
{   
    /** @var integer the level of caching for this controller. Either 0 for none, 1 for views only
     * and 2 for whole pages. This can be overriden by module parameters.*/
    public $cachingLevel = \yiingine\base\CacheControlInterface::CACHE_NONE;
    
    /** @var array group dependecies for cached data.
     * @see CGroupCacheDepdendency.*/
    public $cacheDependencies = array();
    
    /**@var integer the amount of seconds data remains in cache. Used to override settings 
     * from the module.*/
    public $cacheDuration = 8640000; //One hundred days.
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        $this->setSide(Controller::SITE); //We are on the site side.
        
        parent::init(); // Calls the parent's init method first.
        
        // If an announcement should be displayed.
        if(Yii::$app->getParameter('app.announcement'))
        {
            Yii::$app->session->addFlash(\yiingine\widgets\FlashMessage::INFO, [
                'id' => 'announcement',
                'message' => Yii::$app->getParameter('app.announcement')
            ]);
            \yiingine\widgets\admin\AdminOverlay::widget([
                'selector' => '#announcement .message',
                'url' => \yii\helpers\Url::to(['/admin/default/site']), // !!! : À Compléter
                'options' => ['style' => 'z-index:10000;position:fixed;'],
                'displayRule' => '$("#announcement .message").is(":visible");',
                'offsetTop' => '$("#announcement").css("padding-top");',
            ]);
        }
    }
    
    /**
    * Specifies the access control rules.
    * The result of this method is passed to the AccessControl filter.
    * @return array access control rules
    */
    public function accessRules()
    {
        return [[ 'allow' => true ]]; // Everyone can access a SiteController.
    }
    
    /**
     * @return array action filters.
     */
    public function filters()
    {
        //If the cachingLevel permits the level of caching required.
        if($this->cachingLevel === CacheControlInterface::CACHE_ALL)
        {
            // When flash messages are present, do not use caching, rerender the page so they can be displayed
            if(Yii::app()->user->getFlashes(false))
            {
                //Do nothing, just rerender the page entirely.
            }
            //If the current module implements ICacheControl, it can override the caching settings for this controller.
            else if(!in_array('ICacheControl', class_implements($this->module)) || $this->module->getCachingLevel() === ICacheControl::CACHE_ALL)
            {
                //Add the output cache filter.
                $filter = array(
                    'COutputCache',
                    'requestTypes' => array('GET'), //Do not cache POST requests.
                    //Since we also cache the layout, ConfigEntry and MenuItems are added to the dependencies. 
                    'dependency' =>  new CGroupCacheDependency(array_merge($this->cacheDependencies, array('ConfigEntry', 'MenuItem', 'UrlRewritingRule'))),
                    'duration' => in_array('ICacheControl', class_implements($this->module)) ? 
                        $this->module->cacheDuration > $this->cacheDuration ? $this->cacheDuration : 
                            $this->module->cacheDuration : 
                        $this->cacheDuration,
                    'varyByRoute' => true,
                    'varyByExpression' => 'Yii::app()->language.(Yii::app()->user->isGuest ? "1": "0").(Yii::app()->request->isAjaxRequest ? "1": "0")',
                    'varyByParam' => array_keys($this->getActionParams())
                );
        
                return array_merge(parent::filters(), array($filter));
            }
        }
    
        return parent::filters(); //Do not cache.
    }
    
    /** Override of parent implementation to cache the result of the partial rendering.
     *
     * @param string $view the view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * These parameters will not be available in the layout.
     * @return string the rendering result.
     * @throws InvalidParamException if the view file or the layout file does not exist.
     */
    public function render($view, $params = [])
    {
        /*If the result should be returned as a string or if caching is disabled for this controller or
         if caching is disabled module wide or if this is not a GET request.*/
        if( $this->cachingLevel !== \yiingine\base\CacheControlInterface::CACHE_VIEW ||
            (in_array('ICacheControl', class_implements($this->module)) && $this->module->getCachingLevel() === \yiingine\base\CacheControlInterface::CACHE_NONE) ||
            Yii::app()->request->requestType != 'GET' //Only cache GET requests.
        )
        {
            return parent::render($view, $params); // Call normally.
        }

        if($this->beforeRender($view)) //Call the before render event and decide if rendering should proceed.
        {
            $id = 'RenderPartial'. //Build the cache id.
                $this->route.
                Yii::app()->language.
                (Yii::app()->user->isGuest ? '1': '0').
                (Yii::app()->request->isAjaxRequest ? '1': '0').
                implode(array_keys($this->getActionParams())).
                implode(array_values($this->getActionParams()));
            
            //If the output is in not cached.
            if(!$output = Yii::app()->cache->get($id))
            {
                //Rendering result is not in cache.
                $output = $this->renderPartial($view, $data, true); //Call normally.
                
                Yii::app()->cache->set($id, $output, 
                    in_array('ICacheControl', class_implements($this->module)) ?
                        $this->module->cacheDuration > $this->cacheDuration ? $this->cacheDuration :
                            $this->module->cacheDuration :
                        $this->cacheDuration,
                    new CGroupCacheDependency(array_merge($this->cacheDependencies, array('ConfigEntry', 'MenuItem', 'UrlRewritingRule'))));
            }
            
            //If a layout is set, give the output and render it.
            if(($layoutFile = $this->getLayoutFile($this->layout)) !== false )
            {
                $output = $this->renderFile($layoutFile, array('content' => $output), true);
            }
    
            $this->afterRender($view, $output);
    
            $output = $this->processOutput($output);
    
            if($return) //If the result is to be returned as a string.
            {
                return $output;
            }
            echo $output;
        }
    }
}
