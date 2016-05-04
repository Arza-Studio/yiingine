<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\models;

use \Yii;

/**
 * This model represents a log entry for tracking changes active records.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class ActiveRecordLogEntry extends \yii\db\ActiveRecord implements \yiingine\db\DescriptiveInterface
{    
    /**
    * @inheritdoc
    */
    public static function getModelLabel($plural = false)
    {
        return Yii::t(__CLASS__, '{n, plural, =1{ActiveRecord Log Entry}other{ActiveRecord Log Entries}}', ['n' => $plural ? 2 : 1]);
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName() { return 'active_record_changelog'; }
    
    /** 
     * @inheritdoc
     * */
    public function beforeSave($insert)
    {    
        if($insert) //If this is a new record.
        {
            $this->datetime = date(\yiingine\libs\Functions::$MySQLDateTimeFormat);
        }
        else
        {
            throw new \yii\base\Exception('ActiveRecord log entries cannot be modified!');
        }
        
        return parent::beforeSave($insert);
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {        
        /* NOTE: you should only define rules for those attributes that
         * will receive user inputs. */
        return [
            [['model', 'model_id', 'model_table', 'user_name', 'user_id'], 'required'],
            ['user_id', 'integer', 'min' => 0, 'integerOnly' => true],
            /* user_name and user_id's existence are not validated using the user
             * table for performance reason. */ 
            ['attribute', 'required', 'on' => 'ActiveRecordUpdate'],
            ['action', 'default', 'value' => 'CREATE', 'on' => 'ActiveRecordCreate'],
            ['action', 'default', 'value' => 'UPDATE', 'on' => 'ActiveRecordUpdate'],
            ['action', 'default', 'value' => 'DELETE', 'on' => 'ActiveRecordDelete'],
            ['action', 'in', 'range' => ['UPDATE', 'CREATE', 'DELETE']],
            [['action', 'datetime', 'attribute', 'previous_attribute_value', 'new_attribute_value'], 'safe'],
            [['model_title', 'model_admin_url', 'model_id'], 'string', 'min' => 1, 'max' => 255],
            [['model_title', 'model_admin_url'], 'default', 'value' => ''],
            [array_keys($this->attributes), 'safe', 'on' => 'search']
        ];
    }

    /**
     * @inheritdoc
     * */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'action' => Yii::t(__CLASS__, 'Action'),
            'model' => Yii::t(__CLASS__, 'Model'),
            'model_id' => Yii::t(__CLASS__, 'Model ID'),
            'model_table' => Yii::t(__CLASS__, 'Model Table'),
            'model_title' => Yii::t(__CLASS__, 'Model Title'),
            'model_admin_url' => Yii::t(__CLASS__, 'Administration Url'),
            'user_name' => Yii::t(__CLASS__, 'User'),
            'user_id' => Yii::t(__CLASS__, 'User ID'),
            'attribute' => Yii::t(__CLASS__, 'Attribute'),
            'previous_attribute_value' => Yii::t(__CLASS__, 'Previous Attribute Value'),
            'new_attribute_value' => Yii::t(__CLASS__, 'New Attribute Value'),
            'datetime' => Yii::t(__CLASS__, 'Date/Time'),
        ]);
    }
    
    /**
     * @inheritdoc
     * */
    public function attributeDescriptions()
    {
        return [
            'action' => Yii::t(__CLASS__, 'The action on the model.'),
            'model' => Yii::t(__CLASS__, 'The model that was manipulated.'),
            'model_id' => Yii::t(__CLASS__, 'The ID of the model.'),
            'model_table' => Yii::t(__CLASS__, 'The database table of the model.'),
            'model_title' => Yii::t(__CLASS__, 'The title of the model at the time is was modified.'),
            'model_admin_url' => Yii::t(__CLASS__, 'A link to the administration page of the model.'),
            'user_name' => Yii::t(__CLASS__, 'The name of the user that manipulated the record.'),
            'user_id' => Yii::t(__CLASS__, 'The ID of the user that manipulated the record.'),
            'attribute' => Yii::t(__CLASS__, 'The modified attribute.'),
            'previous_attribute_value' => Yii::t(__CLASS__, 'The previous value of the attribute.'),
            'new_attribute_value' => Yii::t(__CLASS__, 'The new value of the attribute.'),
            'datetime' => Yii::t(__CLASS__, 'The date/time of the operation.'),
        ];
    }
    
    /**
     * @inheritdoc
     * */
    public function getAttributeDescription($attribute)
    {
        $descriptions  = $this->attributeDescriptions();
         
        return isset($descriptions[$attribute]) ? $descriptions[$attribute] : '';
    }
    
    /**
     * Creates a data provider instance with search query applied.
     * @param array $params the parameters of the search.
     * @return ActiveDataProvider
     */
    public function search($params)
    {        
        $this->scenario = 'search';
        
        $this->load($params);
        
        return new \yii\data\ActiveDataProvider([
            'query' => self::find()->andFilterWhere([
                    'id' => $this->id,
                    'action' => $this->action,
                    'model_id'=> $this->model_id,
                    'user_id' => $this->user_id
                ])
                ->andFilterWhere(['like', 'model', $this->model])
                ->andFilterWhere(['like', 'model_table', $this->model_table])
                ->andFilterWhere(['like', 'model_title', $this->model_title])
                ->andFilterWhere(['like', 'model_admin_url', $this->model_admin_url])
                ->andFilterWhere(['like', 'user_name', $this->user_name])
                ->andFilterWhere(['like', 'attribute', $this->attribute])
                ->andFilterWhere(['like', 'previous_attribute_value', $this->previous_attribute_value])
                ->andFilterWhere(['like', 'new_attribute_value', $this->new_attribute_value])
                ->andFilterWhere(['like', 'datetime', $this->datetime]),
            'sort' => ['defaultOrder' => ['datetime' => SORT_DESC]]
        ]);
    }
}
