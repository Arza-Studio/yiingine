<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\grid;

use \Yii;
use \yii\helpers\Url;
use \yii\helpers\Html;
use \yiingine\models\MenuItem;

/**
 * Displays the first menu item in breadcrumb format.
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class MenuItemsInfoColumn extends \yii\grid\DataColumn
{    
    /** @var boolean to display or not the link to menus administration forms */
    public $links = true;
    
    /** 
     * @inheritdoc
     * */
    public function init()
    {
        if(!isset($this->filter)) // If no filtering has been defined.
        {
            $this->filter = \yiingine\widgets\admin\MenuTree::widget([
                'side' => \yiingine\models\MenuItem::SITE,
                'model' => $this->grid->filterModel,
                'attribute' => isset($this->name) ? $this->name: 'menu_items',
                'withMenuItems' => true
            ]);
        }
        
        if(!isset($this->attribute))
        {
            $this->attribute = 'menu_items';
        }
        
        if(!isset($this->header)) // The label of the column.
        {
            $this->header = Yii::t(__CLASS__, 'Parent menu');
        }
        
        if(!isset($this->options['style'])) // Use a default style if none is set.
        {
            $this->options['style'] = 'color:gray;font-size:11px;line-height:14px;display:block;font-style:normal;font-weight:normal;';
        }
        
        $this->enableSorting = false; // This column cannot be used to sort.
        
        parent::init();
    }
    
    /** 
     * @inheritdoc
     * */
    protected function renderDataCellContent($model, $key, $index)
    {
        $string = '';
        
        if(!($items = $model->{$this->attribute})) // If there are no menu items.
        {
            return ''; // Nothing to render.
        }
        
        $item = $items[0];
        
        $title = Yii::t(__CLASS__, 'Edit the menu item');
        
        while($item)
        {
            $menuName = MenuItem::makeNameUserFriendly($item->name);
            $string = Html::a($menuName, Url::to($item->getAdminUrl()), ['title' => $title.' : '.$menuName]).$string;
            
            if($item = $item->parent)
            {
                $string = ' > '.$string;
            } 
        }

        return Html::tag('span', $string, ['style' => $this->options['style']]);
    }
}
