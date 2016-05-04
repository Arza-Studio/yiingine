<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\managers;

use \Yii;

/** Manages a CustomField of type AssociatedMenuItemsField.
 * */
class AssociatedMenuItems extends \yiingine\modules\customFields\managers\BaseRelational
{        
    /**
     * @inheritdoc
     * */
    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            \yii\db\ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            \yii\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            \yii\db\ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            \yiingine\db\ActiveRecord::EVENT_AFTER_CLONE => 'afterClone'
        ];
    }
    
    /**
     * @inheritdoc
     * */
    protected function getRelations()
    {
        return [$this->getField()->name => $this->owner->hasMany(\yiingine\models\MenuItem::className(), ['model_id' => 'id'])
            ->where(['model_class' => $this->owner->className()])
            ->orWhere(['model_class' => \yiingine\modules\media\models\Medium::className()])
        ];
    }
    
    /** @var boolean to know if the "enable" attribute was changed.*/
    private $_dbEnable = true;
    
    /** Save the enable attribute to know if it has changed.
     *  @param $event Event the event parameters.*/
    public function afterFind($event)
    {
        $this->_dbEnable = $this->owner->getEnabled();
    }
    
    /** Validate the menu items.
     *  @param $event Event the event parameters.*/
    public function beforeValidate($event)
    {
        $this->setMenuItemsfromPOST();
            
        $success = true;
        //An array containing the parent ids already found. Used for checking for duplicates.
        $parentIds = array();
    
        //For each item this object is managing.
        foreach($this->getMenuItems() as $item)
        {
            if(!$item->validate()) //If the item does not validate.
            {
                $success = false; //Validation failed.
            }
            //If the item's parent_id is already used. (desactivated)
            /*if(in_array($item->parent_id, $parentIds))
            {
            //This means there is a duplicated menu entry.
            $item->addError('id', Yii::t(__CLASS__, 'Duplicated menu entry'));
            $success = false; //Validation failed.
            }*/
    
            array_push($parentIds, $item->parent_id); //Register this item's parent id.
        }
    
        if(!$success) //If validation failed.
        {
            $this->owner->addError($this->manager->getField()->name, Yii::t(__CLASS__, 'Invalid menu items'));
        }
            
        return $success; //Done
    }
    
    /** Save the related menu items.
     *  @param $event Event the event parameters.*/
    public function afterSave($event)
    {
        //For each item to be saved.
        foreach($this->getMenuItems() as $item)
        {
            //Fill the item with data if it is a new item.
            if($item->isNewRecord)
            {
                if($this->owner->type === 'MODULE')
                {
                    //Since MODULE models point to actual modules, the route is generated differently.
                    $item->route = '/'.$this->owner->module_owner_id;
                    $item->parameters = '';
                }
                else //Generate the route using the media module.
                {
                    $item->route = '/media/default/index';
                    $item->parameters = '/id/'.($this->owner->singleton === true ? $this->owner->type : $this->owner->id);
                }
            }
    
            //If the owner is disabled, its menu items should not show up.
            $item->displayed = $this->owner->getEnabled();
    
            $item->model_class = get_class($this->owner);
            $item->model_id = $this->owner->id;
    
            if(!$item->save()) //If saving the menu item failed.
            {
                //dump($menuItem->getErrors());
                throw new \yii\base\Exception(Yii::t(__CLASS__, 'Could not save menu item.'));
            }
        }
            
        // Delete all menu items that have not been saved.
        foreach($this->_menuItemsToDelete as $item)
        {
            $item->delete();
        }
    }
    
    /** Delete all relations the model is part of.
     *  @param $event Event the event parameters.*/
    public function beforeDelete($event)
    {
        foreach($this->getMenuItems() as $item)
        {
            $item->delete();
        }
    }
    
    /**
     * @return array the menu items.
     * */
    public function getMenuItems()
    {
        return $this->owner->{$this->getAttribute()};
    }
    
    /** @var array menu items to delete.*/
    private $_menuItemsToDelete = array();
    
    /** Massively sets attributes on a model.*/
    public function setMenuItemsfromPOST()
    {
        if(!isset($_POST['AssociatedMenuItems'])) //If there is no data for medium Menu items.
        {
            return;
        }
    
        // For each menu item we are trying to set.
        foreach($_POST['AssociatedMenuItems'] as $key => $value)
        {
            $existingModel = null;
            // Check if the attributes are to be assigned to an existing item.
            foreach($this->getMenuItems() as $key => $item)
            {
                // If an item matches the assigned id.
                if(isset($value['id']) && $value['id'] && $item->id == $value['id'])
                {
                    $existingModel = $item; // Use it.
                    if($value['delete'] == 'true') // If the item is to be deleted.
                    {
                        unset($this->_menuItems[$key]); // Remove it from the list so it gets deleted.
                        $this->_menuItemsToDelete[] = $item;
                    }
                }
            }
                
            // Load the attributes into a MenuItem.
            $model = $existingModel ? $existingModel : new MenuItem();
            $model->attributes = $value;
            $model->side = MenuItem::SITE;
                
            if(!$existingModel && $value['delete'] != 'true') //If it is a new entry.
            {
                $this->_menuItems[] = $model;
            }
        }
    }
    
    /** 
     * Triggered when a customizable model is cloned.
     * @param Event $event the cloning event. $event->owner is the clone.
     * */
    public function afterClone($event)
    {
        if(CONSOLE) // If the engine is in CONSOLE mode.
        {
            return; // Cloning relations does not work in CONSOLE mode.
        }
    
        /* Does not work yet, relations cannot be set.
         * 
        if(Yii::$app->request->isPost) // If this is a post request.
        {
            return; // Relations have already been cloned.
        }
    
        $items = [];
    
        // Clone each menu item.
        foreach($this->owner->{$this->getAttribute()} as $item)
        {
            $items[] = clone($item);
        }
        
        // Set the menu items on the cloned model.
        $event->sender->{$this->getAttribute()} = $items;*/
    }
    
    /** 
     * @inheritdoc
     * */
    protected function renderInputInternal()
    {        
        return [
            'type' => '\yiingine\modules\media\widgets\AddMenuItems',
            'model' => $this->owner,
            'menuItems' => $this->owner->{$this->attribute},
            'field' => $this->getField()
        ];
    }
}
