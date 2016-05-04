<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\searchEngine\base;

use \yiingine\modules\searchEngine as module;
use \Yii;

/**
 * An action that conducts searches.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class SearchAction extends \yii\base\Action
{
    /** @var string The view that will be used to render the results. */
    public $view;
    
    /** @var SearchEngine the search engine component to use. */
    public $searchEngine;
    
    /**
    * Action for searching the database with a user submitted query.
    * @param string $query the query to run.
    * @param string $language the language to search.
    * @param string $searchEngine the name of the searchEngine component to use. If left false,
    * the searchEngine configured for the action will be used.
    * @param integer $page the page of the results to show.
    */
    public function run($query, $language, $searchEngine = null, $page = 1)
    {
           // Makes sure $page is an integer.
        $page = (int)$page;
        if(!is_integer($page))
        {
            throw new \yii\web\BadRequestHttpException('Invalid page.');
        }
        
        if($searchEngine) // If a searchEngine was defined.
        {
            $searchEngine = $this->controller->module->$searchEngine;
        }
        else // Use the searchEngine in the object's attributes.
        {
            $searchEngine = $this->searchEngine;
        }
        
        // If the component referred to is not a search Engine.
        if(!($searchEngine instanceof module\components\SearchEngine))
        {
            throw new \yii\web\BadRequestHttpException('Invalid search engine.');
        }
        
        // Fill the model with the received parameters.
        $model = new module\models\Query([
            'searchEngine' => $searchEngine,
            'query' => \yii\helpers\Html::decode($query),
            'language' => $language
        ]);
        
        // For added security, validate the model again.
        if(!$model->validate())
        {
            // Validation failed.
            throw new \yii\web\BadRequestHttpException('Invalid search.');
        }
        
        $searchEngine->page = $page;
        
        $result = $searchEngine->search($model);
        // Yii can't find the route /search/default/index when a different page is requested.
        $result->pagination->route = 'search';
        
        if($page > 1 && !$result->getCount()) // If there are no models on the requested page.
        {
            throw new \yii\web\NotFoundHttpException();
        }
        
        return Yii::$app->view->requireFile($this->view, [
            'result' =>  $result,
            'query' => $model->query    
        ], $this->controller);
    }
}
