<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \Yii;
use \yii\helpers\Html;
use \yiingine\models\admin\AdminParameters;

/**
 * Represents a menu rendered with bootstrap.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class Menu extends \yii\base\Widget 
{
    /** @var boolean if the rendering of the meny should be cached. */
    public $cache = true;
    
    /** @var string the javascript to execute after meni items locking. */
    public $lockMenuItemsCallback;
    
    /* # Menu */
    
    /** @var string the name of the menu. */
    public $menuName = 'menu-name';

    /** @var array Html attributes for the menu's root node <ul> tag. */
    public $menuOptions = [];
    
    /** @var array the menu tree. */
    public $menuTree = [];
    
    /** @var integer the maximum depth at which to render the menu. Set to -1 to render the whole menu. */
    public $menuMaximumDepth = -1;
    
    /* # Nodes <ul> (excluding the the menu's root node) */
    
    /** @var boolean the using a tag grouping the menu items by parents. */ 
    public $listTagDisplay = true;
    
    /** @var string the tag to use for list nodes. */ 
    public $listTag = 'ul';
    
    /** @var array Html attributes for the list tag. */
    public $listOptions = [];
    
    /** @var closure an anonymous function($options, $depth) that will be called to render the list open tag. */
    public $listBeginRendering = null;
    
    /** @var closure an anonymous function($options, $depth) that will be called to render the list end tag. */
    public $listEndRendering = null;
    
    /** @var boolean add node depth into list css classes for easier css targeting. */ 
    public $listIndexClassesDisplay = true;
    
    /* # List items <li> */
    
    /** @var boolean the using a list item wrapping the menu item. */ 
    public $listItemTagDisplay = true;
    
    /** @var string the tag to use for list item nodes. */ 
    public $listItemTag = 'li';
    
    /** @var array Html attributes for the list items <li> tag. */
    public $listItemOptions = [];
    
    /** @var closure an anonymous function($options, $depth, $current, $index) that will be called to render the list item open tag. */
    public $listItemBeginRendering = null;
    
    /** @var closure an anonymous function($options, $depth, $current, $index) that will be called to render the list item end tag. */
    public $listItemEndRendering = null;
    
    /** @var boolean add node depth and the item index into list item css classes for easier css targeting. */ 
    public $listItemIndexClassesDisplay = true;
    
    /** @var string class to add to list item css classes when the current menu item has child. */ 
    public $listItemParentClass = 'parent';
    
    /** @var string class to add to list item css classes when the current menu item is not enabled. */ 
    public $listItemDisabledClass = 'disabled';
    
    /* # Menu items <a>, <span>, ... */
    
    /** @var array Html attributes for the menu items tag. */
    public $menuItemOptions = [];
    
    /** @var closure an anonymous function($text, $url, $options, $depth, $current, $index) that will be called to render the menu item open tag. */
    public $menuItemRendering = null;
    
    /** @var boolean automatically add target=_blank to external urls. Blanked urls open in a different page. */ 
    public $menuItemAutoBlankExternalUrls = true;
    
    /** @var boolean add node depth and the item index into menu item css classes for easier css targeting.. */ 
    public $menuItemIndexClassesDisplay = true;
    
    /**
     * Generates the HTML for a group of nodes, effectively rendering the
     * menu tree.
     * @param array $nodeList the nodes to render.
     * @param integer $depth the menu depth we are at.
     */
    public function renderMenu($nodeList, $depth = 0) 
    {
        # Menu rendering checkings :
        // If the nodesList is empty.
        if(!$nodeList) 
        { 
            return; // No rendering has to be done.
        }
        // If this level should not be rendered.
        if($this->menuMaximumDepth === $depth) 
        {
            return;
        }
        // Checks all nodes to see if they are all to be displayed.
        $allDisplayed = false;
        foreach($nodeList as $node) 
        {
            $model = $data = $node; // For the eval.
            
            // If the node defines a display rule.
            if($node->displayed && $node->rule && !eval('return '.$node->rule . ';'))
            {
                $node->displayed = false;
            }
            $allDisplayed = $allDisplayed || $node->displayed;
        }
        // If there are no nodes to be displayed for this menu, do not render it.
        if(!$allDisplayed) 
        {        
            return;
        }
        
        # Menu's root node opening :
        if($depth === 0)  // If this is the first level of menu.
        {
            // Bulding the first <ul> begin tag html options.
            $options = $this->menuOptions;
            // If there is no id options we use the widget id.
            if(!isset($options['id'])) $options['id'] = $this->id;
            // The name of the menu is added to the class options.
            $options['class'] = (isset($options['class'])) ? $this->menuName.' '.$options['class'] : $this->menuName;
            // List open tag rendering.
            echo Html::beginTag($this->listTag, $options);
        }
        elseif($this->listTagDisplay)
        {
            # List <ul> :
            // Bulding the <ul> tag html options :
            $options = $this->listOptions;
            
            // Add the node depth into css class for easier css targeting.
            if($this->listIndexClassesDisplay)
            {
                $options['class'] = 'depth'.$depth.(!empty($options['class']) ? ' ' : '').$options['class'];
            }
            
            // List open tag rendering.
            if($this->listBeginRendering === null) // If no alternative rendering has been defined.
            {
                echo Html::beginTag($this->listTag, $options);
            }
            else // Use the provided function.
            {
                call_user_func($this->listBeginRendering, $options, $depth);
            }
        }
        
        # List items :
        for($index = 0; $index < count($nodeList); $index++) 
        {
            // Get current list item (MenuItem model)
            $current = $nodeList[$index];
            
            // If the node is not to be displayed.
            if(!$current->displayed)
            {
                continue; // Skip the list item.
            }

            # List item <li> :
            if($this->listItemTagDisplay)
            {
                // Bulding the <li> tag html options :
                $options = $this->listItemOptions;

                // Create the class entry
                if(!isset($options['class']))
                {
                    $options['class'] = '';
                }

                // Add the node depth and the list item index into css class for easier css targeting.
                if($this->listItemIndexClassesDisplay)
                {
                    $options['class'] = 'listItem'.($index+1).' depth'.$depth.(!empty($options['class']) ? ' ' : '').$options['class'];
                }

                // Add the css classes provided by the current MenuItem model.
                if($current->css_class)
                {
                    $options['class'] .= ' '.$current->css_class;
                }

                // Add list item parent class if the menu has child nodes.
                if(!empty($this->listItemParentClass) && $current->displayedMenuItems)
                {
                    $options['class'] .= ' '.$this->listItemParentClass;
                }

                // Add list item disabled class if the menu is not enabled.
                if(!empty($this->listItemDisabledClass) && !$current->enabled)
                {
                    $options['class'] .= ' '.$this->listItemDisabledClass;
                }

                // Node open tag rendering.
                if($this->listItemBeginRendering === null) // If no alternative rendering has been defined.
                {
                    echo Html::beginTag($this->listItemTag, $options);
                }
                else // Use the provided function.
                {
                    call_user_func($this->listItemBeginRendering, $options, $depth, $current, $index);
                }
            }
            
            # Menu item :
            
            // Get the menu item url :
            if(!$current->enabled)
            {
                $url = '#';
            }
            else
            {
                $url = $current->getUrl();
            }
            
            // Bulding the menu item tag html options :
            $options = $this->menuItemOptions;
            
            // Add the target attribute provided by the current MenuItem model.
            if($current->target)
            {
                 $options['target'] = $current->target;
            }
            // If external urls should open in a different page and if the url points to an external site.
            else if($this->menuItemAutoBlankExternalUrls && preg_match("~^(?:f|ht)tps?://~i", $url) && strpos($url, Yii::$app->request->hostInfo) !== 0)
            {
                $options['target'] = '_blank';
            }
            
            // Create the class entry
            if(!isset($options['class']))
            {
                $options['class'] = '';
            }
            // Add the node depth and the list item index into css class for easier css targeting.
            if($this->menuItemIndexClassesDisplay)
            {
                $options['class'] = 'menuItem'.($index+1).' depth'.$depth.(!empty($options['class']) ? ' ' : '').$options['class'];
            }
            
            // Menu item tag rendering.
            if($this->menuItemRendering === null) // If no alternative rendering has been defined.
            {
                echo Html::a($current->name, $url, $options);
            }
            else // Use the provided function.
            {
                call_user_func($this->menuItemRendering, $current->name, $url, $options, $depth, $current, $index);
            }
            echo "\n";
            
            # Sub nodes renderingframes
            // Calls itself to generate that menu items's childrend nodes. 
            // If that menu has no child nodes, the function will simply return.
            $this->renderMenu($current->displayedMenuItems, $depth + 1);

            # List item node end tag rendering
            if($this->listItemTagDisplay)
            {
                if($this->listItemEndRendering === null) // If no alternative rendering has been defined.
                {
                    echo Html::endTag($this->listItemTag);
                }
                else // Use the provided function.
                {
                    call_user_func($this->listItemEndRendering, $options, $depth);
                }
                echo "\n";
            }
        }
        # List node end tag rendering
        if($this->listTagDisplay)
        {
            if($this->listEndRendering === null) // If no alternative rendering has been defined.
            {
                echo Html::endTag($this->listTag);
            }
            else // Use the provided function.
            {
                call_user_func($this->listEndRendering, $options, $depth);
            }
            echo "\n";
        }
        elseif(!$this->listTagDisplay && $depth === 0)
        {
            echo Html::endTag('div');
        }
    }
    
    /** Generate the menu tree. */
    protected function generateMenu()
    {
        // Nothing to do here.
    }
    
    /**
     * Executes the widget.
     * This method is called by {@link CBaseController::endWidget}.
     */
    public function run() 
    { 
        if($this->cache) // On get the role string if caching is enabled.
        {
            $roles = '';
            
            // If auth management is enabled and the user is not a guest. Guests all have the same roles.
            if(!Yii::$app->user->isGuest && Yii::$app->getParameter('enable_auth_management'))
            {
                /* Build a string of the roles for the current user to use as cache id. This is done
                 * because menus will often vary according to permissions. */
                foreach(Yii::$app->authManager->getRoles(Yii::$app->user->id) as $role)
                {
                    $roles .= $role->name;
                }
            }
        }
        
        ob_start();
        
        // If the menu fragment is not in cache.
        if(!$this->cache || // If caching is disabled.
            $this->view->beginCache('renderedMenu', [
                'duration' => 3600, // Cache for one hour.
                'dependency' => new \yiingine\caching\GroupCacheDependency(['MenuItem']),
                'variations' => [
                    Yii::$app->language,
                    $this->menuName,
                    $this->menuMaximumDepth,
                    Yii::$app->user->isGuest ? 0: 1,
                    Yii::$app->user->isGuest ? 0: Yii::$app->user->getIdentity()->superuser,
                    $roles,
                    Yii::$app->controller->getSide() == \yiingine\web\Controller::ADMIN ? Yii::$app->controller->adminDisplayMode : 'x'
                ]
            ])
        )
        {
            if(!$this->menuTree) // If the menu tree has not been generated yet.
            {
                $this->generateMenu(); //Generates the menu tree.
            }
            
            if(!class_exists('AdminParameters'))
            {
                // Make AdminParameters available in the global context so eval'd code can use it.
                class_alias('\yiingine\models\admin\AdminParameters', 'AdminParameters');
            }
                    
            echo $this->renderMenu($this->menuTree);
            
            $this->view->endCache();
        }
        
        MenuAsset::register($this->view);
        
        // Register a script to lock the menu item that is currently being visited.
        $jsReady = 'lockMenuItems("'.$this->menuName.'"';
        $jsReady .= ', "'.Yii::$app->request->url.'"+(window.location.hash ? window.location.hash : "")';
        $jsReady .= ', "'.Yii::$app->request->baseUrl.'"';
        $jsReady .= ', "'.Yii::$app->language.'"';
        $jsReady .= ', "'.\yii\helpers\Url::to(['/']).'"';
        if($this->lockMenuItemsCallback)
        {
            $jsReady .= ', function(){ '.$this->lockMenuItemsCallback.' }';
        }
        $jsReady .= ');';
        $this->view->registerJs($jsReady, \yii\web\View::POS_READY, 'menuItemLocker'.$this->id);
        
        return ob_get_clean();
    }
}

/**
 * The asset bundle for the Menu widget.
 * */
class MenuAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/widgets/assets/';
    
    /** @inheritdoc */
    public $js = ['menu/menu.js'];
    
    /** @inheritdoc */
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ];
}
