<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\models;

use \Yii;

/**
 * This is the model class for table "menus". A row in this table represents
 * a menu item in a database stored menu.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class MenuItem extends \yiingine\db\TranslatableActiveRecord implements \yiingine\db\AdministrableInterface
{    
    /** @var integer for menu item displayed on all sides.*/
    const ALL = 0;
    
    /** @var integer for menu item displayed on the admin side.*/
    const ADMIN = 1;
    
    /** @var integer for menu item displayed on the site side.*/
    const SITE = 2;
    
    /** 
     * @inheritdoc
     * */
    public static function getModelLabel($plural = false)
    {
        return Yii::t(__CLASS__, '{n, plural, =1{Menu item}other{Menu items}}', ['n' => $plural ? 2 : 1]);
    }
    
    /** 
     * @inheritdoc
     * */
    public static function tableName()
    { 
        return 'menus';
    }
    
    /**
     * @inheritdoc
     * */
    public function translatableAttributes()
    {
        return ['name'];
    }
    
    /** 
     * @inheritdoc
     * */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'ActiveRecordOrderingBehavior' => ['class' => '\yiingine\behaviors\ActiveRecordOrderingBehavior', 'groupingAttributes' => ['parent_id']]
        ]);
    }
    
    /** 
     * @inheritdoc
     * */
    public function rules()
    {
        /* NOTE: you should only define rules for those attributes that
         * will receive user inputs. */
        return array_merge(parent::rules(), [
            ['side', 'default', 'value' => self::SITE], // default side for menu items.
            [['name', 'side'], 'required'],
            [['displayed', 'enabled'], 'boolean'],
            [['parent_id', 'position'], 'integer', 'integerOnly' => true, 'min' => 0],
            ['side', 'integer', 'integerOnly' => true, 'max' => self::SITE, 'min' => self::ALL],
            ['side', 'validateSide'],
            ['parent_id', 'exist', 'targetAttribute' => 'id', 'targetClass' => get_class($this), 'when' => function($model){ return $model->parent_id !== 0; }],
            [['name', 'route', 'parameters', 'arguments', 'fragment', 'css_class', 'target'], 'string', 'max' => 255],
            ['rule', 'safe'],
            ['model_id', 'integer', 'integerOnly' => true, 'min' => 0],
            ['model_id', 'validateModelId'],
            ['model_class', 'string', 'min' => 2, 'max' => 255],
            ['position', 'default', 'value' => 1],
            ['model_class', 'validateModelClass'],
            /* The following rule is used by search().
             * Please remove those attributes that should not be searched.*/
            [['parent_id', 'name', 'displayed', 'enabled', 'route', 'parameters', 'arguments', 'fragment', 'side', 'rule', 'model_id', 'model_class', 'css_class', 'target'], 'safe', 'on' => 'search'],
        ]);
    }
    
    /**
     * Validate that a menu item for the admin side is not being added to a 
     * menu item that is for the site and vice-versa.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateSide($attribute, $params)
    {
        if(!$this->parent) // If this menu item is a root model.
        {
            return; // No validation.
        }
        
        if($this->side == self::ALL) // If menu item is for all sides.
        {
            return;
        }
        
        if($this->parent->side == $this->side) // If the sides match.
        {
            return;    
        }
        
        $this->addError('side', Yii::t(__CLASS__, 'Side mismatch. Side must be compatible with parent\'s.'));
    }
    
    /**
     * Validate that the model class exists.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateModelClass($attribute, $params)
    {
        if(!class_exists($this->$attribute)) // If the class does not exist.
        {
            $this->addError($attribute, Yii::t(__CLASS__, 'Model class "{class}" does not exist!', ['class' => $this->$attribute]));
        }
    }
    
    /**
     * Validate that the model id exists.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateModelId($attribute, $params)
    {
        if(!$this->model_class) // If no class was provided.
        {
            return;
        }
        
        $class = $this->model_class;
        
        if(!$class::findOne($this->$attribute)) // If the model does not exist.
        {
            $this->addError($attribute, Yii::t(__CLASS__, 'Model "{id}" with class "{class}" does not exist!', ['class' => $this->model_class, 'id' => $this->$attribute]));
        }
    }
    
    /** 
     * @inheritdoc
     * */
    public function afterDelete()
    {
        $this->deleteMenuTree();
        parent::afterDelete();
    }
    
    /**
     * @return MenuItem the parent of this menu item or null if it does not exist.
     * */
    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_id']);
    }
    
    /** @var array dynamically set children menu items.*/
    public $_menuItems;
    
    /**
     * @return array all this MenuItem's children. If menu items were previously set using setMenuItems()
     * those will be returned.
     * */
    public function getMenuItems()
    {
        return $this->_menuItems ? $this->_menuItems : $this->hasMany(self::className(), ['parent_id' => 'id'])->orderBy('position')->inverseOf('parent');
    }
    
    /** 
     * @param array $models the children menu items.
     * */
    public function setMenuItems($models)
    {
        // Unset relations because BaseActiveRecord keeps them in cache.
        unset($this->menuItems);
        unset($this->displayedMenuItems);
        
        $this->_menuItems = $models;
    }
    
    /**
     * array this MenuItem's children that are displayed.
     * */
    public function getDisplayedMenuItems()
    {
        if($this->_menuItems) // If children menu items were dynamically set.
        {
            $models = [];
            
            // Filter menu items by hand.
            foreach($this->_menuItems as $model)
            {
                if($model->displayed)
                {
                    $models[] = $model;
                }
            }
            
            return $models;
        }
        
        return $this->getMenuItems()->where(['displayed' => 1]);
    }
    
    /**
     * @inheritdoc
     */
    protected function searchInternal($dataProvider)
    {
        $dataProvider = parent::searchInternal($dataProvider);
        
        $dataProvider->query->orderBy([
            'side' => SORT_ASC,
            'parent_id' => SORT_ASC, 
            'position' => SORT_ASC
        ])->with('parent');
        
        return $dataProvider;
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'parent_id' => Yii::t(__CLASS__, 'Parent Menu'),
            'name' => Yii::t(__CLASS__, 'Name'),
            'position' => Yii::t(__CLASS__, 'Position'),
            'displayed' => Yii::t(__CLASS__, 'Displayed'),
            'enabled' => Yii::t(__CLASS__, 'Enabled'),
            'route' => Yii::t(__CLASS__, 'Route'),
            'parameters' => Yii::t(__CLASS__, 'Parameters'),
            'arguments' => Yii::t(__CLASS__, 'Arguments'),
            'fragment' => Yii::t(__CLASS__, 'Fragment'),
            'side' => Yii::t(__CLASS__, 'Display Side'),
            'rule' => Yii::t(__CLASS__, 'Display Rule'),
            'css_class' => Yii::t(__CLASS__, 'CSS Class'),
            'target' => Yii::t(__CLASS__, 'Target'),
            'model_id' => Yii::t(__CLASS__, 'Model ID'),
            'model_class' => Yii::t(__CLASS__, 'Model class'),
        ]);
    }
    
    /**
     * @inheritdoc
     */
    public function attributeDescriptions()
    {
        return array_merge(parent::attributeDescriptions(), [
            'parent_id' => Yii::t(__CLASS__, 'This menu item\'s parent. On the site, this item will be displayed as a child of its parent.'),
            'name' => Yii::t(__CLASS__, 'The display name of this item.'),
            'position' => Yii::t(__CLASS__, 'The position of this item relative to its siblings. Click on "Set on last position" to get the value of the last position + 1 and put this item at the end of the list.'),
            'displayed' => Yii::t(__CLASS__, 'If this menu item should be displayed or hidden.'),
            'enabled' => Yii::t(__CLASS__, 'If this menu item is active or not. A disabled item is displayed but cannot be clicked.'),
            'route' => Yii::t(__CLASS__, 'The route (url) this menu item links to. For internal links, route is a controller/action pair. For external links, the link is in full with the protocol specified (eg: http://).'),
            'parameters' => Yii::t(__CLASS__, 'The request parameters that complete the route all separated by a slash (eg. /id/8/name/foo).'),
            'arguments' => Yii::t(__CLASS__, 'URL arguments without the question mark.'),
            'fragment' => Yii::t(__CLASS__, 'URL fragment without the pound.'),
            'side' => Yii::t(__CLASS__, 'Side on which this menu item will be displayed.'),
            'rule' => Yii::t(__CLASS__, 'PHP code that will be eval\'d to determine if the menu item is visible.'),
            'css_class' => Yii::t(__CLASS__, 'CSS class for the menu item.'),
            'target' => Yii::t(__CLASS__, 'The target attribute of the menu item\'s link.'),
        ]);
    }
    
    /** 
     * Deletes this menu's sub menus.
     * */
    public function deleteMenuTree()
    {
        foreach($this->menuItems as $child)
        {
            $child->deleteMenuTree();
            $child->delete();
        }
    }
    
    /** @return string the URL this menu points to.*/
    public function getURL()
    {
        if(strpos($this->route, '://') !== false) //If the url is external.
        {
             return $this->route; 
        }
        
        $url = [$this->route];
        
        $name = null; //The name of the current parameter.
        //Split the route parameters.
        foreach(explode('/', $this->parameters) as $part)
        {
            if($part == '')
            {
                continue; //Skip empty parts.
            } 
            
            if($name) //If the name of a parameter was found.
            {
                $url[$name] = $part; //This part is the value.
                $name = null;
            }
            else //The current part should be a parameter name. 
            {
                $name = $part;
            }    
        }
        
        return \yii\helpers\Url::to($url).($this->arguments ? '?'.$this->arguments: '').($this->fragment ? '#'.urlencode($this->fragment) : '');
    }
    
    /**
     * @return array replaced names.
     */
    public static function makeNameUserFriendly($name)
    {
        return strtr($name, [
            'mainMenu' => Yii::t(__CLASS__, 'Main Menu'),
            'footerMenu' => Yii::t(__CLASS__, 'Footer Menu'), 
            'headerMenu' => Yii::t(__CLASS__, 'Header Menu'),
            'adminMenu' => Yii::t(__CLASS__, 'Admin Menu'),
        ]);
    }
    
    /** 
     * @param integer the id of the side this menu is for.
     *  @return the translation of the side id to a user friendly name.
     *  */
    public static function sideIdToName($id)
    {
        $arr = [
            MenuItem::ALL => Yii::t(__CLASS__, 'All'),
            MenuItem::ADMIN => Yii::t(__CLASS__, 'Administration'),
            MenuItem::SITE => Yii::t(__CLASS__, 'Site'),
        ];
        return $arr[$id];
    }
    
    /** @return string the url to the admin page for this model.*/
    public function getAdminUrl()
    {
        return \yii\helpers\Url::to(['/admin/menus/update', 'id' => $this->id]);
    }
    
    /** 
     * @inheritdoc
     * */
    public function isAccessible()
    {
        return Yii::$app->authManager->checkAccess(get_class($this).'-view'); //Use normal access checking.    
    }
    
    /** 
     * @inheritdoc
     * */
    public function __toString()
    {
        return $this->name.'('.$this->parent_id.')';
    }
    
    /** 
     * @inheritdoc
     * */
    public function getEnabled()
    {
        return $this->enabled;
    }
}
