<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\searchEngine\widgets;

use \Yii;

/**
 * A widget for displaying an input for submitting queries to the search engine. Many instances of
 * this widget pointing to different search Engines can exist.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class SearchBox extends \yii\base\Widget
{
    /** @var string the route to submit the search query to. */
    public $route = '/search';

    /** @var string the name of the searchEngine component to use. */
    public $searchEngine = 'searchEngine';

    /** @var string the text for the initial value of the text input. */
    public $initialValue = '';

    /** @var string the text for the submit button. */
    public $submitText = null;

    /** @var string the view to render. */
    public $view = 'searchBox';
    
    /** @var string the name of the searchEngine module this widget is associated with. */
    public $module = 'search';
    
    /**
     * @inheritdoc
     */
    public function run() 
    {   
        $model = new \yiingine\modules\searchEngine\models\Query(['searchEngine' => Yii::$app->getModule($this->module)->{$this->searchEngine}]);
        $model->query = $this->initialValue;
        
        // If the user submitted a query though the search input.
        if($model->load(Yii::$app->request->post()))
        {
            $model->language = Yii::$app->language;
            
            if($model->validate()) // If the query was valid.
            {
                // Redirect the query to the controller that is in charge of doing the search.
                Yii::$app->response->redirect([$this->route,
                    'query' => \yii\helpers\Html::encode($model->query),
                    'language' => Yii::$app->language,
                    'searchEngine' => $this->searchEngine
                ])->send();
            }
        }
        // If the widget intercepts a query that was made using it.
        else if(
            isset($_GET['query']) &&
            isset($_GET['searchEngine']) && $_GET['searchEngine'] == $this->searchEngine &&
            Yii::$app->controller->module == Yii::$app->getModule($this->module)
        )
        {     
            // Fill the model so the query shows on the form.
            $model->query = \yii\helpers\Html::decode($_GET['query']);
            $model->language = isset($_GET['language']) ? $_GET['language'] : Yii::$app->language;
        }
        
        // Render the widget using a view so it can be more easily customized.
        return $this->render($this->view, [
            'model' => $model
        ]);
    }

}
