<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\widgets;

use \Yii;
use \yiingine\models\MenuItem;

/**
 * A widget that allows associating menu items to a medium.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class AddMenuItems extends \yii\base\Widget
{
    /** @var ActiveRecord the owner model.*/
    public $model;
    
    /** @var string useless but needed to support rendering by Form.*/
    public $attribute;
    
    /** @var AssociatedMenuItemsField the field for which this widget was created.*/
    public $field;
    
    /** @var array the menu items.*/
    public $menuItems;
    
    /**
    * @inheritdoc
    */
    public function run() 
    {                       
        // If the list of menu items is not in cache.
        if(($menuItemsList = Yii::$app->cache->get(get_class($this).'_menuItemList')) === false)
        {
            // Get the menu items from database.
            $menuItemsList = MenuItem::find()->where(['side' => MenuItem::SITE])->with('menuItems')->all();
            
            // Save the menus in cache.
            Yii::$app->cache->set(get_class($this).'_menuItemList', $menuItemsList, 0, new \yiingine\caching\GroupCacheDependency(['MenuItem']));
        }
            
        return $this->render('addMenuItems', [
            'model' => $this->model,
            'menuItems' => $this->menuItems,
            'menuItemsList' => $menuItemsList
        ]);
    }
}
