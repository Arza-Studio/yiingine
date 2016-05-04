<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\modules\rbac\widgets;

use \Yii;
use \yiingine\modules\users\modules\rbac\models\AuthorizationItem;
use \yii\helpers\Html;

/**
 * A widget that lets a user browser and select authorization items for association.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class AuthorizationItemBrowser extends \yii\base\Widget
{    
    /** @var AuthorizationItem the model authorization items are being associated to.*/
    public $model;
    
    /** @var string the attribute that holds the associated items.*/
    public $attribute;
    
    /**
    * @inheritdoc
    */
    public function run()
    {              
        $model = new AuthorizationItem(null, ['scenario' => 'search']); // Instantiates a model for searching.
        
        if($values = Yii::$app->request->get($model->formName())) // If the request contained search attributes.
        {
            $model->attributes = $values; // Sets them on the model.
        }
        
        // Filter the types to those that can be potential children.
        $availableTypes = AuthorizationItem::getTypeLabels();       
        foreach($availableTypes as $type => $label)
        {
            if($type > $this->model->getType())
            {
                unset($availableTypes[$type]);
            }
        }
        
        $columns = [
            [
                'attribute' => 'name',
                'headerOptions' => ['width' => 75],
            ],
            [
                'attribute' => 'type',
                'headerOptions' => ['width' => 50],
                'filter' => Html::activeDropDownList($model, 'type', $availableTypes , ['prompt' => '']),
                'value' => function($model, $key, $index, $column) { return $model->getTypeLabel(); },
                'visible' => count($availableTypes) > 1
            ],
            [
                'class' => '\yii\grid\CheckboxColumn',
                'options' => ['width' => 25, 'style' => 'text-align:center;'],
                'multiple' => true,
                'checkboxOptions' => function ($model, $key, $index, $column)
                {
                     return [
                         'onclick' => 'this.checked ? children'.$this->id.'.push($(this).attr("value")) : children'.$this->id.'.remove($(this).attr("value"));', 
                         'value' => $model->name,
                         'checked' => Yii::$app->authManager->hasChild($this->model->getItem(), $model->getItem())
                     ];
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}',
                'headerOptions' => ['width' => 70, 'class' => 'button-column'],
                'options' => ['class' => 'button-column'],
                'buttons' => [
                    'update' => function($url, $model, $key)
                    {
                        return $model->name != 'Administrator' ? Html::a('', ["/users/rbac/admin/".mb_strtolower($model->getTypeLabel()), 'id' => $model->getId()], ['title' => Yii::t('generic', 'Update'), 'class' => 'view btnFa fa fa-pencil']) : '';
                    }
                ]
            ]
        ];
        
        $dataProvider = $model->searchPotentialChildren($this->model);
        $dataProvider->getPagination()->setPageSize(10);
        
        // This hidden field will store authorization item associations.
        echo Html::activeHiddenInput($this->model, $this->attribute);
        
        // Define a global variable to hold children.
        Yii::$app->view->registerJs('var children'.$this->id.' =
        {
            array: [],
            set: function(data) { this.array = data; },
            push: function(item) { this.array.push(item); this.updateInput(); },
            remove: function(item) { this.array.splice(this.array.indexOf(item), 1); this.updateInput(); },
            updateInput: function() { $("#'.Html::getInputId($this->model, $this->attribute).'").attr("value", this.array.join(",")); },
            updateGrid: function() 
            {
                for(var i = 0; i < this.array.length; i++)
                {
                    $("input[type=checkbox][value=" + this.array[i] + "]").attr("checked", "checked");
                }
            }
        };', \yii\web\View::POS_HEAD);
        
        // Split the values in a array that will be easier to manipulate.
        Yii::$app->view->registerJs('children'.$this->id.'.set($("#'.Html::getInputId($this->model, $this->attribute).'").attr("value").split(",")); children'.$this->id.'.updateGrid();', \yii\web\View::POS_READY);
        
        echo \yii\grid\GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $model,
            'columns' => $columns
        ]);
    }
}
