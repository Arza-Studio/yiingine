<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\modules\rbac\models;

use \yii\rbac\Item;
use \Yii;

/**
 * This is the model class for a generic authorization item.
 */
class AuthorizationItem extends AuthorizationObject
{    
    // Authorization item type constants.
    const ROLE = \yii\rbac\Item::TYPE_ROLE;
    const PERMISSION = \yii\rbac\Item::TYPE_PERMISSION;
    const UNKNOWN = null;
    
    /** @var string the unique name of rule to be applied to this item.*/
    public $ruleName = '';
    
    /** @var mixed data to be give to the business rule.*/
    public $data = '';
    
    /** @var string a description of the authorization item.*/
    public $description = '';
    
    /** @var integer UNIX timestamp of the item's last update. */
    public $updatedAt = '';
    
    /** @var string this authorization item's children separated by commas.*/
    public $children = '';
    
    /** @var integer the type of this item, only to be set for searching.*/
    private $_typeToSearch = self::UNKNOWN;
    
    /** @var Item the authorization item wrapped by this model.*/
    private $_item;
    
    /** @return array type ID => type label. */
    public static function getTypeLabels()
    {
        return [
            self::PERMISSION => Yii::t(__CLASS__, 'Permission'),
            self::ROLE => Yii::t(__CLASS__, 'Role'),
        ];
    }
    
    /**
     * @param \yii\rbac\Item $item an item to be wrapped by this model.
     * @interitdoc
     * */
    public function __construct($item = null, $config = [])
    {
        parent::__construct($config);
        
        if($item) // If a an item to wrap was provided.
        {
            // If type of the authorization item and the type of the wrapper do not match.
            if($this->getType() !== self::UNKNOWN && $this->getType() !== $item->type)
            {
                throw new \yii\base\Exception('Model class and authorization item mismatch.');
            }
            
            $this->name = $item->name;
            $this->description = $item->description;
            $this->ruleName = $item->ruleName;
            $this->data = $item->data;
            $this->updatedAt = $item->updatedAt;
            $this->createdAt = $item->createdAt;
            $this->isNewRecord = false;
            $this->_item = $item;            
            $this->children = implode(',', array_keys(Yii::$app->authManager->getChildren($this->name)));
            
            $this->trigger(AuthorizationObject::EVENT_AFTER_FIND);
        }
    }
    
    /** 
     * @return Item the item being wrapped by this model.
     * */
    public function getItem()
    {
        return $this->_item;
    }
    
    /** 
     * @return integer the type of the item.
     * */
    public function getType()
    {
        if($this->_item)
        {
            return $this->_item->getType();
        }
        
        return $this->_typeToSearch;
    }
    
    /** 
     * @param integer the type of authorization item to search.
     * */
    public function setType($type)
    {
        if(get_class($this) != self::className()) // Setting of types can only be used with this class.
        {
            throw new \yii\base\Exception('Cannot set a type on '.get_class($this));
        }
        
        $this->_typeToSearch = $type;
    }
    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        /* NOTE: you should only define rules for those attributes that
         * will receive user inputs. */
        return array_merge(parent::rules(), [
            ['description', 'required'],
            ['description', 'string', 'max' => 1000],
            ['name' ,'validateUnique'],
            /* Children can only be added to an existing authorization item. 
             * The reason for this is that the item must already exist in order
             * to verify the associations.*/
            ['children', '\yiingine\validators\UnsafeValidator', 'on' => 'insert'],
            ['children', 'validateChildren'],
            ['ruleName', 'safe'],
            ['data', 'safe'],
            [['description', 'type'], 'safe', 'on' => 'search']
        ]);
    }
    
    /**
     * Validate the name attribute to make sure that it is unique.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */    
    public function validateUnique($attribute, $params)
    {
        // If an authorisation item with this name already exists.
        if($this->getIsNewRecord() && 
            !$this->hasErrors($attribute) && 
            Yii::$app->authManager->getItem($this->$attribute) !== null
        )
        {
            $this->addError($attribute, Yii::t(__CLASS__, 'An authorization item with this name already exists.'));
        }
    }
    
    /**
     * Validate the children.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */    
    public function validateChildren($attribute, $params)
    {
        if($this->hasErrors())
        {
            return; // Only run validation if there are no other errors.
        }
        
        /* 
         * No associations can be made to a new item. 
         * It needs to exist in order to verify its associations. 
         * */
        if($this->getIsNewRecord())
        {
            throw new \yii\base\Exception('A new item cannot have children.');
        }
        
        $children = array_filter(array_unique(explode(',', $this->$attribute)));
        $existingChildren = array_keys(Yii::$app->authManager->getChildren($this->_item->name));
        
        foreach($children as $child)
        {
            if(in_array($child, $existingChildren))
            {
                continue; // Skip associations that already exist.
            }
            
            try // Attempt to add the item has a child.
            {        
                $child = Yii::$app->authManager->getItem($child);
                
                // Use the current name as the new name could be different.
                Yii::$app->authManager->addChild($this->_item, $child);
            }
            catch(\yii\base\Exception $e)
            {
                $this->addError($attribute, $e->getMessage()); // Add the exception as an error.
                continue;
            }
            
            // Item was sucessfully added, remove it for now.
            Yii::$app->authManager->removeItemChild($this->_item, $child);
        }
        
        $this->children = implode(',', $children);
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'description' => Yii::t(__CLASS__, 'Description'),
            'ruleName' => Yii::t(__CLASS__, 'Rule name')
        ]);
    }

    /**
     * @return array customized attribute descriptions (name=>label)
     */
    public function attributeDescriptions()
    {
        return array_merge(parent::attributeDescriptions(), [
            'name' => Yii::t(__CLASS__, 'A name that uniquely identifies the authorization item.'),
            'description' => Yii::t(__CLASS__, 'A short description of the function of the authorization item.'),
        ]);
    }
    
    /** @return string the type label for this item.*/
    public function getTypeLabel()
    {
        $labels = self::getTypeLabels();
        return $labels[$this->getType()];
    }
    
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return \yii\data\ArrayDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        return new \yii\data\ArrayDataProvider([
            'allModels' => $this->_filterChildren(Yii::$app->authManager->getItems($this->getType())),
            'key' => 'name'
        ]);
    }
    
    /** @param \yii\rbac\Item[] $authorizationItems the to get the data from.
     * @return array the filtered children data.*/
    private function _filterChildren($authorizationItems)
    {
        $data = [];
        
        // Convert CAuthItem objects to models while doing a search.
        foreach($authorizationItems as $item)
        {
            // If the items are being searched by name.
            if($this->name && mb_strpos($item->name, $this->name) === false)
            {
                continue; // Skip this item.
            }
            
            // If the items are being searched by description.
            if($this->description && mb_strpos($item->description, $this->description) === false)
            {
                continue; // Skip this item.
            }
            
            if($this->getType() === self::UNKNOWN)
            {
                // Retrieve the class name from the type.
                switch($item->type)
                {
                    case self::ROLE: $class = 'yiingine\modules\users\modules\rbac\models\Role'; break;
                    case self::PERMISSION: $class = 'yiingine\modules\users\modules\rbac\models\Permission'; break;
                    default: throw new \yii\base\Exception('Unknown type'); break;
                }
            }
            else // A specific type is being searched.
            {
                $class = get_class($this);
            }
            
            $data[] = new $class($item);
        }
        
        return $data;
    }
    
    /**
     * Retrieves a list of postentiat children based on the current search/filter conditions.
     * @param AuthorizationItem $parent the authorization item whose children should be searched.
     * @return ArrayDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function searchPotentialChildren($parent)
    {
        // If no specific type is being searched.
        if($this->getType() === self::UNKNOWN || $this->getType() > $parent->getType())
        {
            /* Build the list of potential children according to the type of the parent. For 
             * example, a permission cannot have a role as a children.*/
            switch($parent->getType())
            {
                case self::ROLE:
                    // A role can have roles and permissions as children.
                    $authorizationItems = array_merge(Yii::$app->authManager->getRoles(), Yii::$app->authManager->getPermissions());
                     break;
                case self::PERMISSION:
                    // Permissions can only have other permissions as children.
                    $authorizationItems = Yii::$app->authManager->getPermissions();
                    break;
                default: throw new \yii\base\Exception('Unknown type'); break;
            }
        }
        else
        {
            $authorizationItems = Yii::$app->authManager->getItems($this->getType());
        }
        
        // A parent cannot be its own children.
        unset($authorizationItems[$parent->name]);
        
        return new \yii\data\ArrayDataProvider(
            [
                'allModels' => $this->_filterChildren($authorizationItems),
                'key' => 'name'
            ]
        );
    }
    
    /** 
     * @inheritdoc
     * */
    public function getId()
    {
        return $this->name ? $this->name: self::getModelLabel();
    }
    
    /** 
     * @inheritdoc
     * */
    protected function saveInternal()
    {
        if(get_class($this) == 'AuthorizationItem') // This class cannot be saved.
        {
            throw new CException('Cannot save an '.get_class($this));
        }
        
        if(!$this->validate()) // If the model did not validate.
        {
            return false; // Do not proceed with saving.
        }
        
        if($this->getIsNewRecord()) // If the item is new.
        {
            // It needs to have its item created.
            $item = new self();
            $item->name = $this->name;
            $this->description = $this->description;
            $this->ruleName = $this->ruleName;
            $this->data = $this->data;
            
            // Save the item to persistent storage.
            $this->add($this->name, $this->_item);
        }
        else // The item already exists.
        {
            // Note: modifying the item's attributes causes it to update itself in storage.
            $this->_item->name = $this->name;
            $this->_item->description = $this->description;
            $this->_item->ruleName = $this->ruleName;
            $this->_item->data = $this->data;
            
            // Get the current children.
            $currentChildren = array_keys(Yii::$app->authManager->getChildren($this->name));
            $newChildren = array_filter(array_unique(explode(',', $this->children)));
            
            // Add or remove the associations.
            foreach($newChildren as $child)
            {
                if(in_array($child, $currentChildren)) // If the association already exists.
                {
                    continue; // Skip it.
                }
                
                Yii::$app->authManager->addChild($this->_item, Yii::$app->authManager->getItem($child));
            }
            
            // Remove the associations that have been deleted.
            foreach(array_diff($currentChildren, $newChildren) as $child)
            {
                Yii::$app->authManager->removeChild($this->_item, Yii::$app->authManager->getItem($child));
            }
            
            // Save the item to persistent storage.
            Yii::$app->authManager->update($this->name, $this->_item);
        }
        
        return true;
    }
    
    /** 
     * @inheritdoc
     * */
    protected function deleteInternal()
    {
        if($this->formName() == 'AuthorizationItem') // This class cannot be deleted.
        {
            throw new \yii\base\Exception('Cannot delete an '.get_class($this));
        }
        
        if($this->getIsNewRecord()) // If item is new.
        {
            throw new \yii\base\Exception('Cannot delete a new item.');
        }
        
        return Yii::$app->authManager->removeItem($this->_item);
    }
}
