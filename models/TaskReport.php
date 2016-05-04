<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\models;

use \Yii;

/**
 * This is the model class for table "task_reports". This model represents the 
 * result of running an automated task.
 */
class TaskReport extends \yii\db\ActiveRecord
{
    // Task statuses.
    const STATUS_FAILED = 0;
    const STATUS_DONE = 1;
    const STATUS_DONE_WITH_WARNINGS = 2;
    const STATUS_OVERDUE = 3;
    const STATUS_UNKNOWN = 4;
     
    /**
     * @return string the associated database table name
     */
    public static function tableName() { return 'task_reports'; }
    
    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if($insert) //If this is a new record.
        {
            // Sets the execution date attribute.
            $this->execution_date = date(\yiingine\libs\Functions::$MySQLDateTimeFormat);
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
        return array(
            [['task_id', 'status'], 'required'],
            ['status', 'integer', 'integerOnly' => true, 'min' => 0],
            ['report', 'safe'],
            [['id', 'task_id', 'status', 'report', 'execution_date'], 'safe', 'on' => 'search'],
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => Yii::t(__CLASS__, 'Task'),
            'status' => Yii::t(__CLASS__, 'Status'),
            'report' => Yii::t(__CLASS__, 'Report'),
            'execution_date' => Yii::t(__CLASS__,'Execution Date'),
        ];
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
        
        $query = self::find()->orderBy('execution_date DESC')
            ->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['like', 'task_id', $this->task_id])
            ->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['like', 'execution_date', $this->execution_date])
            ->andFilterWhere(['like', 'report', $this->report]);
        
        return new \yii\data\ActiveDataProvider(['query' => $query]);
    }
    
    /** @param integer $code the code to translate.
     * @return string the textual name of a status code.*/
    public static function getStatusName($code)
    {
        switch($code)
        {
            case self::STATUS_FAILED: return Yii::t(__CLASS__, 'Failed');
            case self::STATUS_DONE:  return Yii::t(__CLASS__, 'Done');
            case self::STATUS_DONE_WITH_WARNINGS: return Yii::t(__CLASS__, 'Done with warnings');
            case self::STATUS_OVERDUE : return Yii::t(__CLASS__, 'Overdue');
            case self::STATUS_UNKNOWN : return Yii::t(__CLASS__, 'Unknown');
            default:
                throw new \yii\base\Exception('Invalid status: '.$this->status);
        }
    }
}
