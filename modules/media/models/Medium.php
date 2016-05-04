<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\models;

use \Yii;
use \yiingine\modules\customFields\models\CustomField;
use \yiingine\libs\Functions;

/** Medium model class. A Medium is an abstract model meant to be displayed. It is customised
 * through the addition of CustomFields.
 * @see CustomFields.*/
class Medium extends \yiingine\modules\customFields\models\CustomizableModel implements \yiingine\db\ViewableInterface, \yiingine\db\AdministrableInterface, \yiingine\modules\searchEngine\models\SearchableInterface
{                    
    /**
     * @var boolean if only on instance of this model can exist at any given time.
     * */
    public static $singleton = false;
    
    /**
     * @var boolean if this type should be included in site maps.
     * */
    public static $includeInSiteMap = false;
    
    /** 
     * @var string the name of the module this medium belongs to.
     * */
    public $module;    
    
    /**
    * @inheritdoc
    */
    public static function getModelLabel($plural = false)
    {
        return Yii::t(__CLASS__, '{n, plural, =1{Medium}other{Medias}}', ['n' => $plural ? 2 : 1]);
    }
    
    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'media';
    }

    /**
     * @inheritdoc
     * */
    public static function find()
    {
        // Override of parent implementation to use a different ActiveQuery class.
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }
    
    /**
     * Find all enabled models.
     * @return ActiveQuery
     * */
    public static function findEnabled()
    {
        $query = static::find();
        $query->enabledOnly = true;
        return $query;
    }
    
    /**
     * @inheritdoc
     * */
    public function __construct($config = [])
    {
        if(!isset($config['module']))
        {
            $config['module'] = 'media'; // This model belongs to the media module by default.
        }
        
        parent::__construct($config);
        
        $this->type = self::className();
    }
    
    
    /** @var array a cache for the attribute names of this model;*/
    private $_attributes = [];
    
    /**
     * @inheritdoc
     */
    public function attributes()
    {
        // Override of parent implementation to return attributes that belong to this type only.
        if(!$this->_attributes || CONSOLE) // If this is the first time attributes are being fetched.
        {
            $this->_attributes = ['id', 'view', 'type', 'dt_crtd', 'ts_updt'];
            
            foreach($this->getManagers() as $name => $manager)
            {
                if(!$manager->getField()->getSql()) // If the field does not have a column.
                {
                    continue; // It is most likely represented by a relation.
                }
                
                $this->_attributes[] = $name;
            }
        }
        
        return $this->_attributes;
    }
    
    /**
     * @inheritdoc
     */
    public function hasAttribute($name)
    {
        // Override of parent implementation to only look for attributes that belong to this type.
        return in_array($name, $this->attributes());
    }
    
    /**
     * @inheritdoc
     * */
    protected function getFieldModels()
    {
        CustomField::$module = Yii::$app->getModule('media')->getModule('mediaFields');
        
        $cacheId = 'AllMediaFields_'.self::className().'_'.$this->getScenario();
        
        if(isset(CustomField::$cache[$cacheId]))
        {
            return CustomField::$cache[$cacheId];
        }
        
        // Attempt to fetch the CustomFields for this type from cache.
        if(($fields = Yii::$app->cache->get($cacheId)) === false)
        {
            $fields = CustomField::find(Yii::$app->getModule('media')->getModule('mediaFields'))->with('formGroup')->where(['like', 'owners', $this->formName()])->all();
            
            // Save the retrieved fields in the application cache.
            Yii::$app->cache->set($cacheId, $fields, 0, new \yiingine\caching\GroupCacheDependency(['CustomField', 'FormGroup']));
        }
        
        CustomField::$cache[$cacheId] = $fields; // Save the fields in the runtime cache.
        
        return $fields;
    }
    
    /**
     * @inheritdoc 
     * */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        $rules = [
            ['type', '\yiingine\validators\UnsafeValidator'] // Type is automatically set.
        ];
        
        //If this medium is configured to work with views.
        if($this->getViews() !== false)
        {
            //Add validation for the view attribute.
            $rules[] = [['view', 'type'], 'string', 'max' => 63];
            $rules[] = ['view', 'default', 'value' => function($model, $attribute)
            { 
                $views = $model->getViews();
               
                //If no views are configured for this media type.
                if(!$model->getViews())
                {
                    throw new \yii\base\Exception(Yii::t(__CLASS__, 'No view set for media of type {type}', ['{type}' => self::className()]));
                }
                
                return $views[0]['path']; // Use the first view by default.    
            }];
            $rules[] = ['view', 'validateView'];
            $rules[] = ['view', 'required'];        
        }
        else // Views are not used.
        {
            $rules[] = ['view', '\yiingine\validators\UnsafeValidator'];
        }
        
        if(static::$singleton) // If there can only be one instance of this record.
        {
            $rules[] = ['type', 'unique', 'message' => Yii::t(__CLASS__ , '{type} is singleton. Cannot create more than one record of type {type}.', ['type' => $this->type])];    
        }
        
        // Add rules from managers to account for scenarios.
        foreach($this->getManagers() as $manager)
        {
            foreach($manager->rules() as $rule)
            {
                if($manager->getField()->availability)
                {
                    $rule['on'] = array_filter(array_map('trim', explode(',', $manager->getField()->availability)));
                }
                
                $rules[] = $rule; // Add rule normally.
            }
        }
        
        return array_merge($rules, \yiingine\db\TranslatableActiveRecord::rules());    
    }
    
    /**
     * Validate the view field. It must be within the configured views for this type.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateView($attribute, $params)
    {        
        $views = $this->getViews();
        
        //If no views are configured for this media type.
        if(!$views)
        {
            throw new \yii\base\Exception(Yii::t(__CLASS__, 'No view set for media of type {type}', ['type' => self::className()]));
        }
        
        $found = false; //If the view was found.

        foreach($views as $view)
        {
            if($this->$attribute == $view['path'])
            {
                $found = true; //View was found.
                break;
            }
        }
        
        if(!$found) //If the view was no found.
        {
            //Add an error.
            $this->addError($attribute, Yii::t(__CLASS__, 'View is not within the configured views.'));
        }
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'view' => Yii::t(__CLASS__, 'View'),
            'type' => Yii::t(__CLASS__, 'Type'),
        ]); 
    }
    
    /** 
     * @return CustomFieldsModule the module for this model's custom fields.
     * */
    public function getCustomFieldsModule()
    {
        return Yii::$app->getModule('media')->getModule('mediaFields');
    }

    /**
     * @var string when a resourcename cannot be generated, a unique id is return and it
     * gets saved here so it is only generated once during runtime.
     * */
    private $_uniqueId = null;
    
    /** 
     * Returns an encoded name for this medium. An encoded name is in the
     * form of id-fileNameEncodedTitle.
     * @param boolean translate if the title should be translated.
     * @return string the resource name of the medium.
     * */
    public function getResourceName($translate = true)
    {
        if(static::$singleton)
        {
            return $this->formName(); // Singletons are identified by their type.
        } 
        if(!$this->isNewRecord)
        {            
            if($translate) //If the title should be translated.
            {
                $title = $this->getTitle();
            }
            else
            {
                $language = Yii::$app->language; //Save the current language.
                Yii::$app->language = Yii::$app->getBaseLanguage();
                $title = $this->getTitle();
                Yii::$app->language = $language; //Restore the current language.
            }
            
            return $this->id.'-'.Functions::encodeFileName($title);
        }
        else //Cannot form a proper resource name so just return a unique name.
        {
            if(!$this->_uniqueId){ $this->_uniqueId = uniqid(); }
            return  $this->_uniqueId;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function getTitle($html = false)
    {
        $class = lcfirst($this->formName());
        
        if(!$this->hasAttribute($class.'_title') || !$this->{$class.'_title'})
        {
            return false;
        }
        
        return $html ? $this->{$class.'_title'} : strip_tags(str_replace(['<br>', '<br/>', '<br />'], ' ', $this->{$class.'_title'}));
    }
    
    /**
     * @inheritdoc
     */
    public function getDescriptor()
    {
        // Display a default descriptor.
        $descriptor = ucfirst(mb_strtolower(static::getModelLabel()));
        if($this->isNewRecord)
        {
            return $descriptor; // Do not add more information if this is a new record.
        }
        
        return $descriptor.' > '.$this->getTitle().' ('.$this->primaryKey.')';
    }
    
    /**
     * @inheritdoc
     */
    public function getThumbnail()
    {         
        if(!$this->hasAttribute('thumbnail'))
        {
            return false; // This medium does not use thumbnails.
        }
        
        return $this->thumbnail ? $this->getManager('thumbnail')->getFileUrl() : Yii::$app->getParameter('yiingine.SocialMetas.meta_thumbnail') ? ['/user/assets/'.Yii::$app->getParameter('yiingine.SocialMetas.meta_thumbnail')] : false;
    }
    
    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->hasAttribute(strtolower($this->formName()).'_content') ? $this->{strtolower($this->formName()).'_content'}: 'No content';
    }
    
    /**
     * @inheritdoc
     * */
    public function getDescription()
    {
        return $this->hasAttribute('description') && $this->description ? $this->description : $this->getContent();   
    }
    
    /**
     * @inheritdoc
     */
    public function getAdminUrl()
    {        
        // Build the basic route to this model.
        $url = ['/'.$this->module.'/admin/'.lcfirst(parent::formName()).($this->isNewRecord || !$this->scenario ? '/create' : '/update')];
          
        // The route differs if the model is new or not.
        if(!$this->isNewRecord)
        {
            $url['id'] = $this->id;
        }
        
        return $url;
    }
    
    /**
     * @inheritdoc
     */
    public function isAccessible()
    {
        $action = $this->isNewRecord ? 'create': 'view';
        
        if(get_class($this) == __CLASS__) // Access checking is different for this class.
        {
            return Yii::$app->user->can('Medium-'.$action) || Yii::$app->user->can($this->formName().'-'.$this->type.'-'.$action);
        }
        
        return Yii::$app->user->can($this->formName().'-'.$action); //Use normal access checking.
    }
    
    
    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return static::getModelLabel().': '.$this->getTitle().($this->getEnabled() ? '': ' ('.Yii::t('generic', 'Disabled').')');
    }
    
    /**
     * @inheritdoc
     */
    public function getEnabled()
    {
        return $this->hasAttribute('enabled') ? $this->enabled: true;
    }
    
    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->view ? ['/media/default/index/', 'id' => $this->id]: ['', 'modal' => $this->id];
    }
    
    /**
     * @inheritdoc
     */
    public static function getSearchableAttributes()
    {
        return [];
    }
    
    /**
     * A list of views that this medium can use.
     * [
     *     'path' => a path to the view
     *  'title' => an array (of languages) or a string
     *  'description' => an array (of languages) or a string decribing the view.
     * ]
     * 
     * @return array|false the views of false if the medium cannot be viewed.
     * */
    public static function getViews()
    {
        return false;
    }
    
    /**
     * @inheritdoc
     */
    public static function instantiate($row)
    {    
        // Fill the constructor attributes only if we are using pure media types.
        if(self::className() == __CLASS__)
        {
            // Get the class from the type.
            $class = $row['type'];
            
            return new $class();
        } 
       
        return parent::instantiate($row);
    }
}

/** 
 * An active query adapted for use with the medium model.
 */
class ActiveQuery extends \yii\db\ActiveQuery
{
    /** @var boolean if only enabled models whould be retrieved.*/
    public $enabledOnly = false;
    
    /**
     * @var closure a function that will be called during prepare();
     * */
    public $prepareCallback = null;
    
    /**
     * @inheritdoc
     * */
    public function prepare($builder)
    {
        // If a specific media class is being used.
        if($this->modelClass != Medium::className())
        {
            $this->andWhere(['type' => $this->modelClass]);
        }
        
        if($this->enabledOnly)
        {
            $this->andWhere(['enabled' => 1]);
        }
        
        if($this->prepareCallback)
        {
            call_user_func($this->prepareCallback, $this);
        }
        
        return parent::prepare($builder);
    }
    
    /**
     * Executes query and returns all enabled models results as an array.
     * @param Connection $db the DB connection used to create the DB command.
     * If null, the DB connection returned by [[modelClass]] will be used.
     * @return array|ActiveRecord[] the query results. If the query results in nothing, an empty array will be returned.
     */
    public function allEnabled($db = null)
    {
        $this->andWhere(['enabled' => 1]);
        return parent::all($db);
    }
}
