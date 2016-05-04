<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\db;

use \Yii;

/**
 * A base model class for all models for use within the yiingine and especially those
 * that are meant to be administered from the amdinistration interface.
 * 
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
abstract class ActiveRecord extends \yii\db\ActiveRecord implements \yiingine\db\DescriptiveInterface, \yiingine\db\ModelInterface
{
    const EVENT_AFTER_CLONE = 'afterClone';
    
    /** @var the relations that should be made part of the search.*/
    protected $relationSearches = [];
    
    /**
     * @return string the short (unqualified (ie: without the namespace)) class name.
     * */
    public static function shortClassName()
    {
        return substr(self::className(), strrpos(self::className(), '\\') + 1);
    }
    
    /** 
     * @inheritdoc
     * */
    public function behaviors()
    {        
        return [
            'ActiveRecordLogBehavior' => '\yiingine\behaviors\ActiveRecordLogBehavior',
            'ActiveRecordCachingBehavior' => '\yiingine\behaviors\ActiveRecordCachingBehavior',
            'ActiveRecordTimeStampingBehavior' => '\yiingine\behaviors\ActiveRecordTimeStampingBehavior',
            //'ActiveRecordLockingBehavior' => '\yiingine\behaviors\ActiveRecordLockingBehavior'
        ];
    }
    
    /**
     * Called automatically by PHP after the model is cloned. This method takes care of 
     * reinitializing certain values of the cloned model.
     * */
    public function __clone()
    {
        // Make the model appear as if it was a new record.
        foreach($this->primaryKey() as $key)
        {
            $this->$key = null;
        }
        
        $this->isNewRecord = true;
        $this->dt_crtd = null;
        $this->ts_updt = null;
        
        $this->trigger(self::EVENT_AFTER_CLONE);
        
        // Reattaches the behaviors because they pointed to the model cloned from.
        $this->detachBehaviors();
        // Remove all event registration manually because behaviors removed their event listeners on the model cloned from.
        foreach((new \ReflectionClass(self::className()))->getConstants() as $name => $constant)
        {
            if(strpos($name, 'EVENT_') === 0) // If the constant begins with EVENT_.
            {
                $this->off($constant); // Remove all handlers to this event.
            }
        }
        $this->attachBehaviors($this->behaviors());
    }
    
    /**
    * @inheritdoc
    */
    public function __get($name)
    {   
        //If the name contains a dot a relations' attribute is being accessed during search.
        if(($pos = strpos($name, '.')) && $this->scenario == 'search')
        {
            $this->getRelated(substr($name, 0, $pos)); //Will throw an exception if the relation does not exist.
            return isset($this->relationSearches[$name]) ? $this->relationSearches[$name]: '';
        } 
        
        return parent::__get($name);
    }
    
    /**
     * @inheritdoc
     * */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['id', 'ts_updt', 'dt_crtd'], 'safe', 'on' => 'search']
        ]);  
    }
    
    /**
     * @inheritdoc
     * */
    public function save($runValidation = true, $attributeNames = null)
    {
        /* Since models used with the search scenario have a special status, they
         * are explicitely prevented from saving themselves. */
        if($this->scenario == 'search')
        {
            throw new \yii\base\Exception('Cannot save models used for searching.');
        }
        
        return parent::save($runValidation, $attributeNames);
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = ['id' => 'ID' ];
        
        if(!($behavior = $this->getBehavior('ActiveRecordTimeStampingBehavior'))) // If this behavior is not attached.
        {
            return $labels;
        }
        
        return array_merge($behavior->attributeLabels(), $labels);
    }
    
    /** 
     * @return array a user friendly description of this model's attributes.
     * */
    public function attributeDescriptions()
    {
        $descriptions = ['id' => Yii::t(__CLASS__, 'A unique database identifier.')];
        
        if(!($behavior = $this->getBehavior('ActiveRecordTimeStampingBehavior'))) // If this behavior is not attached.
        {
            return $descriptions;
        }
        
        return array_merge($behavior->attributeDescriptions(), $descriptions);
    }
    
    /** 
     * @param string $attribute the name of the attribute from which a description is needed. 
     * @return string the description.
     * */
    public function getAttributeDescription($attribute)
    {
        $descriptions  = $this->attributeDescriptions();
        
        return isset($descriptions[$attribute]) ? $descriptions[$attribute] : '';
    }
    
    /**
     * @inheritdoc
     */
    public function safeAttributes()
    {
        /* Override of parent implementation to detect the presence of \yiingine\validators\UnsafeValidator
         * and mark the attribute as unsafe.*/
        
        $attributes = parent::safeAttributes();
    
        foreach($this->getValidators() as $validator)
        {
            if($validator instanceof \yiingine\validators\UnsafeValidator)
            {
                $attributes = array_diff($attributes, $validator->attributes);
            }
        }
    
        return $attributes;
    }
    
    /**
     * Creates a data provider instance with search query applied.
     * @param array $params the parameters of the search.
     * @return ActiveDataProvider
     */
    public final function search($params)
    {
        $this->scenario = 'search';
        
        $this->load($params);
        
        return $this->searchInternal(new \yii\data\ActiveDataProvider(['query' => static::find()]));
    }
    
    /**
     * Tailor the search to this model.
     * @param array $dataProvider the dataProvider that will be used for the search.
     * @return array the modified configuration array.
     */
    protected function searchInternal($dataProvider)
    {
        $columns = $this->getTableSchema()->columns;
        
        foreach($this->getAttributes($this->safeAttributes()) as $attribute => $value)
        {
            if(!isset($columns[$attribute])) // If the attribute has no column in the database.
            {
                continue; // It cannot be searched.
            }
            
            // Add each attribute's condition depending on its type.
            switch($columns[$attribute]->type)
            {
                case 'boolean':
                case 'smallint':
                case 'integer':
                case 'bigint':
                case 'float':
                case 'decimal':
                    $dataProvider->query->andFilterWhere([$attribute => $value]);
                    break;
                default: // The search is done with an SQL LIKE.
                    $dataProvider->query->andFilterWhere(['like', $attribute, $value]);
            }
        }
        
        return $dataProvider;
    }
    
    /** 
     * @inheritdoc
     * @param boolean $deleteEach execute delete() on each model.
     */
    public static function deleteAll($condition = '', $params = [], $deleteEach = false)
    {
        if(!$deleteEach)
        {
            return parent::deleteAll($condition, $params);
        }
        
        $count = 0;
        
        foreach(static::find()->andWhere($condition)->params($params)->batch(100) as $models)
        {
            foreach($models as $model)
            {
                $count++;   
                $model->delete(); // Use the delete() method so events are ran.
            }
        }
        
        return $count; // Return the number of rows affected.
    }
    
    /** 
     * @inheritdoc
     * @param boolean $updateEach execute save() on each model.
     */
    public static function updateAll($attributes, $condition = '', $params = [], $updateEach = false)
    {
        if(!$updateEach)
        {
            return parent::updateAll($attributes, $condition, $params);
        }
        
        $count = 0;
        
        foreach(static::find()->andWhere($condition)->params($params)->batch(100) as $models)
        {
            foreach($models as $model)
            {
                $count++;
                $model->attributes = $attributes;     
                $model->save(); // Use the save method so events are ran.
            }
        }
        
        return $count; // Return the number of rows affected.
    }
}
