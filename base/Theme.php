<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\base;

use \Yii;

/**
 * Class for theming the yiingine.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 * */
class Theme extends \yii\base\Theme
{
    /**
     * @var string the class name of the asset bundle that goes with this theme.
     * */
    public $assetBundle;
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        $this->setBasePath(dirname((new \ReflectionClass($this))->getFileName()));
        $this->setBaseUrl(Yii::$app->assetManager->publish($this->basePath.DIRECTORY_SEPARATOR.'web')[1]);
        
        parent::init();
        
        // Set the pathmap so themes can override both yiingine and client site views.
        $this->pathMap[Yii::getAlias('@yiingine')] = $this->basePath;
        $this->pathMap[Yii::$app->basePath] = $this->basePath;
    }
    
    /**
     * Register assets for the theme.
     * @param \yii\base\View $view the view to register assets with.
     * */
    public function register($view)
    {
        if($this->assetBundle !== null)
        {
            $view->registerAssetBundle($this->assetBundle);
        }
    }
}
