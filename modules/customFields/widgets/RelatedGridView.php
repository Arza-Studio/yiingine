<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\widgets;

use \Yii;
use \yii\helpers\Html;
use \yii\helpers\Url;

/**
 * This widget is a grid view for managing association between models.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class RelatedGridView extends \yii\base\Widget
{    
    /** @var CustomizableModel the model this widget belongs to. */
    public $model;

    /** @var string the name of the attribute this widget is an input of.*/
    public $attribute;
    
    /** @var array the field configuration. */
    public $configuration = [];
    
    /** @var array the classes available for association.*/
    public $availableClasses;
    
    /** @var array the list of related models.*/
    public $relatedModels = [];
    
    /** @return array the list of actions used by this widget.*/
    public static function actions()
    {
        return [
            'search' => ['class' => '\yiingine\modules\customFields\widgets\ModelSearchAction']
        ];    
    }
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        if(!Yii::$app->getModule('search')) // This widget requires the search module.
        {
            throw new \yii\base\Exception('The '.self::className().' requires the searchEngine component to be installed.');
        }
        
        parent::init();
    }
    
    /**
    * @inheritdoc
    */
    public function run() 
    {                                       
        return $this->render('relatedGridView', [
            'model' => $this->model,
            'attribute' => $this->attribute,
            'configuration' => $this->configuration,
            'availableClasses' => $this->availableClasses,
            'relatedModels' => $this->relatedModels
        ]);
    }
    
    /** 
     * @return array the columns for the gridview that will present the relations.
     * */
    public function getColumns()
    {
        return [
            [
                'filter' => false,
                'attribute' => 'id',
                'value' => function ($model, $key, $index, $column){ return $model->id.':'.$model->formName(); },
                'headerOptions' => ['width' => 20, 'style' => 'text-align:center;'],
                'options' => ['style' => 'color:#7f7f7f;text-align:center;padding:3px 0;'],
                'contentOptions' => ['class' => 'idCell']
            ],
            [
                'value' => function ($model, $key, $index, $column){
                    return $model->getThumbnail() ? Html::tag('img', '', ['src' => Url::to($model->getThumbnail()), 'width' => 50]) : '-';
                },
                'header' => Yii::t(__CLASS__, 'Thumbnail'),
                'headerOptions' => ['width' => '50','style' => 'text-align:center;'],
                'options' => ['style' => 'text-align:center;color:#7f7f7f;padding:3px 0;'],
                'format' => 'raw'
            ],
            [
                'value' => function ($model, $key, $index, $column){
                    return Html::tag('span', $model->getModelLabel(), ['class' => 'type', 'style' => 'display:block;font-size:10px;line-height:10px;color:#7f7f7f;']).
                    Html::tag('span', $model->getTitle(), ['class' => 'title']);
                },
                'header' => Yii::t(__CLASS__, 'Title'),
                'headerOptions' => ['width' => '200'],
                'options' => ['style' => 'padding-top:7px;padding-bottom:4px;padding-right:0;'],
                'format'=>'raw',
            ]
        ];
    }
    
    /** 
     * @param ActiveRecord $model the model that owns the gridview.
     * @param View $view the view rendering the widget.
     * @return array the button columns for the gridview. 
     * */
    public function getButtons($model, $view)
    {
        $view->params['model'] = $model;
        
        // The position and edition columns are made separate for better clarity.
        return [
            [ // The column for positionning buttons.
                'class' => 'yii\grid\ActionColumn',
                'template' => '{up}{down}',
                'headerOptions' => ['width' => 45, 'class' => 'button-column'],
                'buttons' => [
                    'up' => function($url, $model, $key) 
                    {
                        return $model->hasAttribute('position') ? Html::a('', '',  ['class' => 'view btnFa fa fa-chevron-up commentedBtn noLoader', 'title' => Yii::t('generic', 'Up'), 'onclick' => '
                            var row = $(this).parent().parent();
                            row.insertBefore(row.prev());
                            rebuildRelatedList($("#'.$this->attribute.'-list"), $("[name=\"'.Html::getInputName($this->model, $this->attribute.'_related').'\"]"));
                        ']) : '';
                    }, 
                    'down' => function($url, $model, $key) 
                    {
                        return $model->hasAttribute('position') ? Html::a('', '',  ['class' => 'view btnFa fa fa-chevron-down commentedBtn noLoader', 'title' => Yii::t('generic', 'Down'), 'onclick' => '
                            var row = $(this).parent().parent();
                            row.insertAfter(row.next());
                            rebuildRelatedList($("#'.$this->attribute.'-list"), $("[name=\"'.Html::getInputName($this->model, $this->attribute.'_related').'\"]"));
                        ']) : '';
                    } 
                ]
            ],
            [ // The column for link, edit and delete buttons.
                'class' => 'yii\grid\ActionColumn',
                'template' => Yii::$app->controller->getSide() === \yiingine\web\Controller::ADMIN ? '{updateRelated}{link}{deleteRelated}': '{deleteRelated}',
                'headerOptions' => ['width' => 60, 'class' => 'button-column'],
                'buttons' => [
                    'updateRelated' => function($url, $model, $key) 
                    {
                        // Recursive modification == bad idea.
                        return (int)$model->primaryKey !== (int)Yii::$app->view->params['model']->primaryKey && !Yii::$app->request->get('ajaxModify') ?
                            Html::a('', Url::to($model->getAdminUrl()),  ['class' => 'view btnFa fa fa-pencil commentedBtn noLoader', 'title' => Yii::t('generic', 'Update'), 'onclick' => '
                                this.preventDefault();
                                var id = $(this).parent().parent().children(":first-child").text();
                                var type = $(this).parent().parent().children(":nth-child(3n)").find(".type").text();
                                var title = $(this).parent().parent().children(":nth-child(3n)").find(".title").text();
                                var options = new Array();
                                options["title"] = type+" : "+title;
                                var editor = new AjaxModelEditor($(this).prop("href"), function(){$("#'.$this->attribute.'-list").yiiGridView("update");}, options);
                                editor.open();
                            ']) 
                        : '';
                    },
                    'deleteRelated' => function($url, $model, $key) 
                    {
                        // Recursive modification == bad idea.
                        return Html::tag('span', '',  ['class' => 'view btnFa fa fa-trash commentedBtn noLoader', 'title' => Yii::t('generic', 'Delete'), 'onclick' => '
                            $(this).parent().parent().remove(); rebuildRelatedList($("#'.$this->attribute.'-list"), $("[name=\"'.Html::getInputName($this->model, $this->attribute.'_related').'\"]")); return false;
                        ']);
                    },
                    'link' => function($url, $model, $key) 
                    {
                        // Recursive modification == bad idea.
                            return Html::a('', Url::to($model->getAdminUrl()),  ['class' => 'view btnFa fa fa-dot-circle-o commentedBtn', 'title' => Yii::t(__CLASS__, 'View in other page')]);
                    } 
                ]
            ]
        ];
    }
}

/** 
 * An action that allows searching models available for custom field relations.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 * */
class ModelSearchAction extends \yiingine\modules\searchEngine\base\SearchAction
{
    /* @var FieldManager the manager for the relation. **/
    public $manager;
    
    /* @inheritdoc **/
    public $view = '@yiingine/modules/searchEngine/components/views/jsonSearchResult.php';
    
    /**
    * Action for searching the database with a user submitted query.
    * @param string $query the term to search.
    * @param string $language the language to search, leave null for the current language.
    * @param string $searchEngine not used here but needed for compatibility with parent method.
    * @param integer $page not used here but needed for compatibility with parent method.
    */
    public function run($query = '', $language = null, $searchEngine = null, $page = 1)
    {
        if(!$this->manager) // If the manager has not been provided.
        {
            throw new \yii\web\ServerErrorHttpException('Manager not provided');
        }
        
        if(!$classes = $this->manager->getAvailableModelClasses())
        {
            throw new \yii\web\ForbiddenHttpException(); // Forbidden, no search possible.
        }
        
        $models = [];
        
        foreach($classes as $class)
        {
            $model = $class['model'];
            $models[] = $model;
        }
        
        // Configure the search engine.
        $this->searchEngine = new \yiingine\modules\searchEngine\base\SearchEngine([
            'models' => $models,
            'resultsPerPage' => 20,
            'minimumQueryLength' => 0
        ]);
        
        return parent::run($query, $language === null ? Yii::$app->language: $language, null, 1);
    }
}
