<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets\admin;

use \Yii;
use \yiingine\models\MenuItem;

/**
 * Generates a dropdown list with menu items indented and colorized according 
 * to their position in the menu.
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class MenuTree extends \yii\base\Widget
{   
    /** @var Model the model that owns this menu item field */
    public $model;
    
    /** @var string the attribute storing the menu item id.*/
    public $attribute;
    
    /** @var string the styles of the dropdown list.*/
    public $style = '';
    
    /** @var integer if the menu items should belong a certain side. Leave
     * null for all sides. */
    public $side = null;
    
    /** @var boolean if only items with sub items should be displayed. */
    public $withMenuItems = false;
    
    /** @var array HTML optiions to pass to the dropdown list.*/
    public $options = ['class' => 'form-control'];
    
    /**
    * @inheritdoc
    */ 
    public function run() 
    {
        $listData = []; // Contains data for the dropDownList.
        $listOptions = []; // Will contain options for the dropDownList data.
        
        // If the menuitems cannot be found in cache.
        if(($menuItems = Yii::$app->cache->get('menuItemsTree'.(string)$this->side)) === false)
        {
            $where = ['parent_id' => 0];
            
            if($this->side)
            {
                $where['side'] = $this->side;
            }
            
            $menuItems = MenuItem::find()->where($where)->with('menuItems')->all();
            
            // Save the query result in cache.
            Yii::$app->cache->set('menuItemsTree'.(string)$this->side, $menuItems, 0, new \yiingine\caching\GroupCacheDependency([MenuItem::className()]));
        }
        
        $listData[0] = Yii::t(__CLASS__, 'No parent');
        
        // Generate the list data for the menus.
        $this->_generateTree($listData, $listOptions, $menuItems, 0);
        
        if(isset($this->model))
        {
            if(!$this->model->isNewRecord && $this->model instanceof MenuItem) // If the model already exists.
            {
                // Disable its line and highlight it.
                $listOptions[$this->model->id]['disabled'] = true;

                $listOptions[$this->model->id]['style'] = 'color:white;background-color:'.Yii::$app->adminPalette->get('Gray', -80).';';
            }
            
            echo \yii\helpers\Html::activeDropDownList($this->model, $this->attribute, $listData, array_merge($this->options, [
                'style' => $this->style,
                'options' => $listOptions,
            ]));
        }
        else
        {
            echo \yii\helpers\Html::dropDownList($this->id, '', $listData, array_merge($this->options, [
                'id' => $this->id,
                'style' => $this->style,
                'options' => $listOptions
            ])); 
        }
    }
    
    /** Generate listData and listOptions for the menu tree by indenting an coloring the
     * different levels of the hierarchy.
     * @param array $listData the menus items
     * @param array $listOptions options to add to the list item
     * @param array $items the menus items to generate a list item for
     * @param int $level the depth in the menu tree
     */
    private function _generateTree(&$listData, &$listOptions, $items, $level)
    {        
        foreach($items as $item) 
        {            
            if($this->withMenuItems && !$item->menuItems)
            {
                continue;
            }
            
            $indent = '';
            for($i = 0; $i < $level; $i++, $indent .= '....'); // Build the indent level.
            $listData[$item->id] = $item->name;
            // Progressively lighten the color of the menu items.
            $listOptions[$item->id] = ['style' => 'color:'.Yii::$app->adminPalette->get('Gray',-95 + ($level * 15)).';', 'label' => $indent.strip_tags(MenuItem::makeNameUserFriendly($item->name))]; 
            // Recursive call to go down the hierarchy.             
            $this->_generateTree($listData, $listOptions, $item->menuItems, $level+1);
        }
    }
}
