<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets\admin;

use \Yii;

/**
 * A widget that allows an integer field to be set at the last
 * value of a group of database objects according to a related field criteria.
 * The fields managed by this widget are useful for imposing a certain order to a group of database 
 * records such as a position.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class PositionManager extends \yii\base\Widget
{    
    /** @var string the name of the first related attribute used in grouping values.*/
    public $relatedAttribute = '';
    
    /** @var string the value of the first related attribute if it is not part of a form.*/
    public $relatedValue = '';
    
        /** @var string the name of the second related attribute used in grouping values.*/
    public $relatedAttribute1 = '';
    
    /** @var string the value of the second related attribute if it is not part of a form.*/
    public $relatedValue1 = '';
    
        /** @var string the name of the third related attribute used in grouping values.*/
    public $relatedAttribute2 = '';
    
    /** @var string the value of the third related attribute if it is not part of a form.*/
    public $relatedValue2 = '';
    
    /** @var string the name of the value attribute.*/
    public $attribute = 'position';
    
    /** @var Model the model whose value is being set.*/
    public $model;
    
    /** @var integer the size of the value text field.*/
    public $size = 3;
    
    /** @var integer the maximum length of the value text field.*/
    public $maxLength = 3;
    
    /** @var array action parameters to add to the url.*/
    public $actionParams = [];
    
    /** @return array the list of actions used by this widget.*/
    public static function actions()
    {
        return [
            'positionManager.getLastAvailableValue' => ['class' => '\yiingine\widgets\admin\GetLastAvailableValueAction'],
            'positionManager.moveValue' => ['class' => '\yiingine\widgets\admin\MoveValueAction']
        ];    
    }
    
    /**
     * @inheritdoc
     * */
    public function run() 
    {
        return $this->render('positionManager');
    }
}

/** Base class for PositionManager's action
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>*/
abstract class PositionManagerAction extends \yii\base\Action
{
    /** 
     * @inheritdoc 
     * */
    public function init()
    {
        parent::init();
        
        /* Since some methods of \yiingine\web\admin\ModelController are needed, we need to make
         * sure it is the current controller.*/
        if(!(Yii::$app->controller instanceof \yiingine\web\admin\ModelController))
        {
            // Cannot proceed.
            throw new \yii\base\Exception('The current controller is not an instace of \yiingine\web\admin\ModelController');
        }
    }
}

/** Fetches the next available integer value for an arbitrary database column according to a 
 * related attribute. This action may useful with position fields or for knowing the next
 * available integer primary key.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> */
class GetLastAvailableValueAction extends PositionManagerAction
{
    /**
     * Runs the action.
     * @param string $attribute the name of the attribute for which the next value is fetched.
     * @param boolean $isNewRecord if the record is new.
     * @param string $relatedAttribute the name of the first related attribute that groups the values.
     * @param mixed $relatedValue the value of the first related attribute.
     * @param string $relatedAttribute1 the name of the second related attribute that groups the values.
     * @param mixed $relatedValue1 the value of the second related attribute.
     * @param string $relatedAttribute2 the name of the third related attribute that groups the values.
     * @param mixed $relatedValue2 the value of the third related attribute.
     */
    public function run($attribute, $isNewRecord,
        $relatedAttribute = null, $relatedValue = null, 
        $relatedAttribute1 = null, $relatedValue1 = null, 
        $relatedAttribute2 = null, $relatedValue2 = null
    )
    {
        /*
         * NOTE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!
         * 
         * The ActiveRecordGroupingBehavior cannot be used here because the next value
         * is queried dynamically using the related attribute's value from the form.
         * This way, the position can be changed to its last value if the related attribute
         * has changed.
         * */
        
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $instance = Yii::$app->controller->model(); // Creates an instance of that model.
        $query = (new \yii\db\Query()) // Creates the query that will fetch the next value.
            ->select('MAX('.$attribute.') as max')
            ->from($instance->tableName());
        
        if($relatedAttribute) // If there is a related attribute.
        {
            if($relatedValue) // If a related value was provided.
            {
                // Add the realted attribute to the query.
                $query->andWhere($relatedAttribute.'=:val', [':val' => $relatedValue]);
            }
        }
        
        if($relatedAttribute1) // If there is a second related attribute.
        {
            if($relatedValue1) // If a related value was provided.
            {
                // Add the realted attribute to the query.
                $query->andWhere($relatedAttribute1.'=:val1', [':val1' => $relatedValue1]);
            }
        }
        
        if($relatedAttribute2) // If there is a thirs related attribute.
        {
            if($relatedValue2) // If a related value was provided.
            {
                // Add the realted attribute to the query.
                $query->andWhere($relatedAttribute2.'=:val2', [':val2' => $relatedValue2]);
            }
        }
        
        $result = $query->one();

        $result = (int)$result['max']; //Extract the result and convert is to an integer.
        
        if(!is_integer($result)) //If the value returned is not an integer.
        {
            // The attribute field is not an integer type.
            throw new \yii\web\BadRequestHttpException($attribute.' is not an integer');
        }
        
        /* If the record is new, add one to the last position. If not, use the position of
         * the last item. This will effectively shift all other items down one position.*/
        
        return $result + ($isNewRecord ? 1 : 0);
    }
}

/** Moves an attribute up or down.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> */
class MoveValueAction extends PositionManagerAction
{
    /** 
     * @inheritdoc 
     * */
    public function run()
    {
        if(!Yii::$app->request->isPost)
        {
            throw new \yii\web\MethodNotAllowedHttpException();
        }
        if(Yii::$app->controller->singleton) 
        { 
            throw new \yii\web\ForbiddenHttpException(); // Singletons cannot be moved;
        }
        
        $validator = \yii\base\DynamicModel::validateData(['direction' => Yii::$app->request->post('direction'), 'attribute' => Yii::$app->request->post('attribute'), 'id' => Yii::$app->request->post('id')], [
            ['direction', 'in', 'range' => [0, 1]],
            ['attribute', 'in', 'range' => array_keys(Yii::$app->controller->model()->getAttributes())],
            ['id', 'integer', 'integerOnly' => true, 'min' => 1]
        ]);
        
        if($validator->hasErrors())
        {
            throw new \yii\web\BadRequestHttpException();
        }
        
        if(!($model = Yii::$app->controller->loadModel($validator->id)))
        {
            throw new \yii\web\NotFoundHttpException(); // The model was not found.
        }
        
        // Disable active record locking for this request.
        $model->detachBehavior('ActiveRecordLockingBehavior');
        
        if(!isset($model->{$validator->attribute}))
        {
            throw new \yii\web\NotFoundHttpException(); // The attribute was not found.
        }
        
        // If the model has ActiveRecordOrderingBehavior, it will take care of shifting of models that are in the way.
        $model->{$validator->attribute} += $validator->direction ? 1 : -1;
        if(!$model->save()) // If the model failed to save.
        {
            throw new \yii\web\ServerErrorHttpException('Errors on the model.');
        }
        
        return Yii::$app->controller->actionIndex(); // Use the index action to render the grid view again.
    }
}
