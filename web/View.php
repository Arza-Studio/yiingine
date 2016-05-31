<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\web;

use \Yii;

/**
 * This class overrides Yii2's implementation of the view component for use with the yiingine.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class View extends \yii\web\View
{    
    /**
     * @var string|array The theme object, a theme name included in $availableSiteThemes
     *  or the configuration for creating the theme object for the site.
     * */
    public $siteTheme;
    
    /**
     * @var array a list of configurations for available site themes
     */
    public $availableSiteThemes = [];
    
    /**
     * @var string|array The theme object or the configuration for creating the theme object for the admin.
     * */
    public $adminTheme;
    
    /**
     * @var array a list of configurations for available admin themes
     */
    public $availableAdminThemes = [];
    
    /**
     * @inheritdoc
     * */
    public function beginPage()
    {
        if($this->theme instanceof \yiingine\base\Theme)
        {
            $this->theme->register($this); // Register assets for the active theme.
        }
        
        parent::beginPage();
    }
    
    /**
     * Override of parent implementation to look for view files within the yiingine if they
     * are not present in a theme or the site.
     * @inheritdoc
     */
    public function render($view, $params = [], $context = null)
    {
        if(!$this->theme) // If no global theme is currently set.
        {
            // Check if a theme has been set for the site or the admin.
             
            // Select the theme attribute depending on what part of the application is in use.
            $theme = Yii::$app->controller instanceof \yiingine\web\admin\Controller ? 'adminTheme': 'siteTheme';
        
            // If a configuration entry for a theme exists.
            if($name = Yii::$app->getParameter('app.'.\yii\helpers\Inflector::underscore($theme)))
            {
                $this->$theme = $name;
            }
             
            if(YII_DEBUG) // Allow theme switching in debug mode.
            {
                if($name = Yii::$app->request->get($theme)) // If a theme was provided with the request.
                {
                    $this->$theme = $name;
                    unset($_GET[$theme]);
                }
            }
             
            if(is_array($this->$theme))
            {
                $theme = $this->$theme;
                if(!isset($theme['class']))
                {
                    $theme['class'] = '\yiingine\base\Theme';
                }
                $this->theme = Yii::createObject($theme);
            }
            else if(is_string($this->$theme))
            {
                // Select the available themes depending on what part of the application is in use.
                $availableThemes = Yii::$app->controller instanceof \yiingine\web\admin\Controller ? $this->availableAdminThemes: $this->availableSiteThemes;
                 
                if(strpos($this->$theme, '\\') !== false) // If the theme is a class.
                {
                    $this->theme = Yii::createObject($this->$theme);
                }
                // The theme is the name of a theme contained in $availableThemes.
                else if(isset($availableThemes[$this->$theme]))
                {
                    $this->$theme = $availableThemes[$this->$theme];
                        
                    // If the selected theme is again the name of a theme.
                    if(is_string($this->$theme) && strpos($this->$theme, '\\') === false)
                    {
                        // Throw and error to prevent recursing into this method.
                        throw new \yii\base\Exception($this->$theme.' is not a valid theme!');
                    }
        
                    $this->theme = Yii::createObject($this->$theme);
                }
                else
                {
                    throw new \yii\base\Exception($this->$theme.' is not a valid theme!');
                }
            }
        }
        
        $viewFile = $this->findViewFile($view, $context); // Attempt to render the file using a view in the application.
        
        if(!is_file($viewFile)) // If the view could not be found.
        {
            // Use the view in the yiingine.
            $viewFile = str_replace(Yii::getAlias('@app'), Yii::getAlias('@yiingine'), $viewFile);
        }
        
        return parent::renderFile($viewFile, $params, $context);
    }
    
    /**
     * Override of parent implementation to look for view files within the yiingine if they
     * are not present in a theme or the site.
     * @inheritdoc
     */
    public function renderFile($viewFile, $params = [], $context = null)
    {
        $viewFile = Yii::getAlias($viewFile);
        
        if(!is_file($viewFile)) // If the view could not be found.
        {
            // Use the view in the yiingine.
            $viewFile = str_replace(Yii::getAlias('@app'), Yii::getAlias('@yiingine'), $viewFile);
        }
        
        return parent::renderFile($viewFile, $params, $context);
    }
    
    /**
     * Requires a php file using Yii's view path syntax.
     * @param string $path the path to the file.
     * @param array $params the parameters to be passed to the file.
     * @param object $context the context used to require the file.
     * @return the result of the requiring of the file.
     * */
    public function requireFile($path, $params = [], $context = null)
    {
        $file = $this->findViewFile($path, $context); // Attempt to render the file using a view in the application.
        
        if(!is_file($file)) // If the file could not be found.
        {
            // Use the file in the yiingine.
            $file = str_replace(Yii::getAlias('@app'), Yii::getAlias('@yiingine'), $file);
        }
        
        $this->context = $context;
        
        extract($params, EXTR_OVERWRITE);
        
        return require($file);
    }
}
