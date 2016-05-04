<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\grid;

use \Yii;
use \yii\helpers\Html;
use \yii\helpers\Url;

/**
 * A column for displaying the position of an item and allows a user to change it.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class PositionColumn extends \yii\grid\DataColumn
{        
    /** @var array the last position cache retrieved from a given query. */
    private static $_lastPositions = [];
    
    /** @var array the HTML options for the header cell tag. */
    public $headerOptions = ['width' => 80, 'style' => 'text-align:center;'];
    
    /** @var array the HTML options for the data cell tags.*/
    public $contentOptions =  ['class' => 'positionColumn'];
    
    /** @var callback a function($query, $model) that applies conditions to the query.*/
    public $query;
    
    /** @var array the query parameters to add to the move request.*/
    private $_queryParams;
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        if(!isset($this->attribute))
        {
            $this->attribute = 'position';
        }
        
        parent::init();
        
        $this->_queryParams = Yii::$app->request->queryParams;
        unset($this->_queryParams['_pjax']);
    }
    
    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        # Value
        $value = Html::tag('span' , $model->{$this->attribute}, ['class' => 'positionColumnValue']).'&nbsp;';
        
        if(!Yii::$app->controller->checkAccess('update'))
        {
            return; // Do not display buttons if the user is not allowed to edit the models.
        }
        
        // If the position of the model is 0, it cannot be moved.
        if((int)$model->{$this->attribute} === 0)
        {
            return;
        }
        
        # Up Button
        // Do not display the up button if position is already at 0.
            $value .= Html::a(\rmrevin\yii\fontawesome\FA::icon('chevron-up'), '#', [
                'class' => 'btn btn-primary btn-xs'.((int)$model->{$this->attribute} !== 1 ? '': ' disabled'), 
                'title' => Yii::t('generic', 'Up'),
                'onclick' => '$.pjax({
                    type: "POST",
                    url: "'.\yii\helpers\Url::to(array_merge(['positionManager.moveValue'], $this->_queryParams)).'",
                    container: "#w0",
                    push: false,
                    data: '.\yii\helpers\JSON::encode(['id' => $model->id, 'attribute' => $this->attribute, 'direction' => 0]).'
                }); return false;'
            ]);
        
        $query = $model::find();
        
        # Down Button
        // Do not display the down button if position is already at last value.
        if($this->query) // Apply the query callback.
        {
            call_user_func($this->query, $query, $model);
        }
        
        $cacheId = '#'.(is_array($query->where) ? implode($query->where): 'noWhere').implode(array_keys($query->params)).implode($query->params);
        // # is added so the string is not empty.
        
        if(isset(self::$_lastPositions[$cacheId])) // If this last position was previously retrieved.
        {
            $lastPosition = self::$_lastPositions[$cacheId];
        }
        else
        {    
            // Fetch the position of the last model.
            $lastPositionQuery = (new \yii\db\Query())
                ->select('MAX('.$this->attribute.') as max')
                ->from($model->tableName())
                ->where($query->where, $query->params)
                ->createCommand()
                ->queryOne();
            $lastPosition = isset($lastPositionQuery['max']) ? (int)$lastPositionQuery['max'] : 1 ;
            
            self::$_lastPositions[$cacheId] = $lastPosition;
        }
        
            $value .= Html::a(\rmrevin\yii\fontawesome\FA::icon('chevron-down'), '#', [
                'class' => 'btn btn-primary btn-xs'.((int)$model->{$this->attribute} < $lastPosition ? '' : ' disabled'), 
                'title' => Yii::t('generic', 'Down'),
                'onclick' => '$.pjax({
                    type: "POST",
                    url: "'.\yii\helpers\Url::to(array_merge(['positionManager.moveValue'], $this->_queryParams)).'",
                    container: "#w0",
                    push: false,
                    data: '.\yii\helpers\JSON::encode(['id' => $model->id, 'attribute' => $this->attribute, 'direction' => 1]).'
                }); return false;'
            ]);
        
        /*// DISABLED because more that one record is affected by this operation.
         * if(isset($model->ActiveRecordLockingBehavior)) // If active record locking is enabled.
        {
            // Add the last update time to the request.
            $postData[$model->ActiveRecordLockingBehavior->getId()] = $model->ts_updt;
        }*/
        
        return $value;
    }
}
