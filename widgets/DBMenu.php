<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \Yii;
use \yiingine\models\MenuItem;

/**
 * Represents a menu that is stored in database.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class DBMenu extends Menu
{
    /** @var array nodes that will be prepended to the menu. Allow the addition
     * extra nodes for menu rendered from the database.*/
    public $beforeNodes = null;
    
    /** @var array nodes that will be appended to the menu. Allow the addition
    * extra nodes for menu rendered from the database.*/
    public $afterNodes = null;
    
    /** @var integer if the root menu item cannot be pointed to by its name such as when
     * different menu items have the same name, it can be pointed to by its id.*/
    public $menuItemId = null;
    
    /**
     * Generates the menu tree.
     */
    protected function generateMenu()
    {
        //Attempts to fecth the tree from cache.
        if(($this->menuTree = Yii::$app->cache->get('menu_'.$this->menuName.Yii::$app->language)) === false)
        {
            // Fetches the root model of that tree from database matching that menu name or its id.
            $rootModel = $this->menuItemId ?
                [MenuItem::findOne($this->menuItemId)] :
                // Fetches all menu items with this name to detect conflicts.
                MenuItem::find()->where(['name' => $this->menuName])->all();
            
            if(!$rootModel) //If the root model for this menu was not found.
            {
                throw new \yii\base\Exception('Missing root model for DBMenu '.($this->menuItemId ? $this->menuItemId : $this->menuName));    
            }
            
            if(count($rootModel) > 1) // If there is a conflict with the menu's name.
            {
                throw new \yii\base\Exception('More than one menu item have are named "'.$this->menuName.'", use the menuItemId attribute to point to the root menu instead.');
            }
            
            $rootModel = array_pop($rootModel); // The array contains only one menu item.
            
            //Lazy load the whole menu tree to cache it.
            $lazyLoad = function($menuItem) use (&$lazyLoad)
            {
                foreach($menuItem->displayedMenuItems as $item)
                {
                    $lazyLoad($item);
                }
            };
            $lazyLoad($rootModel);
            
            $this->menuTree = $rootModel->displayedMenuItems;
            
            // Save the tree in cache.
            Yii::$app->cache->set('menu_'.$this->menuName.Yii::$app->language, $this->menuTree, 0, new \yiingine\caching\GroupCacheDependency(['MenuItem', 'URLRewritingRule']));
        }
        
        // If there are nodes to be appended to the tree.
        if($this->afterNodes)
        {
            //Merge them to the end of the tree.
            $this->menuTree = array_merge($this->menuTree, $this->afterNodes);
        }
        // If there are nodes to be added at before the tree.
        if($this->beforeNodes)
        {
            //Merge them to the beginning of the tree.
            $this->menuTree = array_merge($this->beforeNodes, $this->menuTree);
        }
        
        parent::generateMenu();
    }
}
