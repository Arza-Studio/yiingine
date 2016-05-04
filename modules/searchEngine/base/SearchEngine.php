<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\searchEngine\base;

use \Yii;
use \yii\base\Exception;

/**
 * A component for searching models. This function has been made separate from a controller to
 * allow searches in console mode or to present the results using a different manner than that
 * prescribed by the extension's controller.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class SearchEngine extends \yii\base\Component
{            
    /** @var array a list of the models to search. If the name is given as expression => array(), the items
     * in the array are the attributes to search and the expression is a php statement that generates a model
     * to work with. Models should be ordered by priority of search.*/
    public $models = [];
    
    /** @var integer the page of the results. */
    public $page = 1;
    
    /** @var integer the number of results to present on a page. */
    public $resultsPerPage = 10;
    
    /** @var integer the minimum length of a query.*/
    public $minimumQueryLength = 3;
    
    /** Executes a search on the models configured to be searchable.
     * @param SearchQuery $query the query to execute.
     * @return ArrayDataProvider and array of the results that have been found:
     *  array(
     *  1 => weight,
     *  2 => model,
     *  3 => attributes that were searched
     * )
     * */
    public function search($query)
    {
        $results = []; // The models found.
        $terms = $query->formatQuery();
        
        $priority = count($this->models);
        foreach($this->models as $model => $attributes)
        {
            // Get an instance of the model.
            if(is_integer($model))
            {
                $model = new $attributes();
                
                // If the model does not implement SearchableInteface.
                if(!in_array('yiingine\modules\searchEngine\models\SearchableInterface', class_implements($model)))
                {
                    // It cannot be searched.
                    throw new \yii\base\Exception($model::className().' does not implement SearchableInterface.');    
                }
                
                try
                {
                    // If the model should not be searched.
                    if(($attributes = $model->getSearchableAttributes()) === false)
                    {
                        continue; // Skipt it.
                    }
                }
                catch(Exception $e) // The exception can be debugged here if needed.
                {
                    throw $e;
                }
            }
            else // The model is the key.
            {
                $model = new $model();
            }
            
            // Implementation of the ViewableInterface is required to display the model.
            if(!($model instanceof \yiingine\db\ViewableInterface))
            {
                throw new Exception(get_class($model).' does not implements ViewableInterface.');
            }    
            
            $modelQuery = $model->find();
            $modelQuery->limit = 10; // For performance resasons, limit results to 10.
            
            /**TODO find a way to paginate the results.*/ 
            
            foreach($attributes as $attribute) // For each attribute that can be searched.
            {
                // Adapt searching depending on the type of the attribute bein searched.
                switch($model->getTableSchema()->columns[$attribute]->type)
                {
                    case 'integer': // Use number search
                    case 'float':
                        foreach($terms as $term) // For each term in the search.
                        {                    
                             $modelQuery->orWhere([$attribute => $term]);
                        }
                        break;
                    default: // Use textual search.
                        foreach($terms as $term) // For each term in the search.
                        {                                                
                            // If a language different that the base language is being searched and the model is translatable.
                            if($query->language !== Yii::$app->getBaseLanguage() && $model instanceof \yiingine\db\TranslatableActiveRecord)
                            {
                                if(isset($model->getTranslationAttributes($attribute)[$query->language]))
                                {
                                    $attribute = $model->getTranslationAttributes($attribute)[$query->language];
                                }
                            }

                            $modelQuery->orWhere(['like', $attribute, $term]);
                        }
                        break;
                }
            }
            
            $weightedResult = [];
            
            foreach($modelQuery->all() as $result) // For each found model.
            {
                if($result instanceof \yiingine\db\AdministrableInterface)
                {
                    if(!$result->getEnabled()) // If the model has been disabled.
                    {
                        continue;
                    }
                }
                
                $weight = 0; // Add a weight to each search result.
                
                foreach($attributes as $attribute) // For each attribute that can be searched.
                {
                    foreach($terms as $term) // For each term in the search.
                    {                    
                        if(!$result->$attribute) // If the attribute is empty.
                        {
                            continue;
                        }
                        
                        // TODO trouver une manière plus scientifique de donner du poid au résulat.
                        
                        $termWeight = mb_substr_count($result->$attribute, $term);
                        
                        if($term === $query->query) // If the term was the exact query.
                        {
                            // Give it extra weight.
                            $termWeight *= 2;
                        }
                        
                        $weight += $termWeight;
                    }
                }
                
                /* Add the priority of the model to the weight so models that are more
                important appear first. */
                $weight += $priority;
                
                $weightedResult[] = [$weight, $result, $attributes];
            }
            
            $results = array_merge($results, $weightedResult);
            $priority--;
        }
        
        usort($results, function($a, $b)
        {
            if($a[0] > $b[0]){ return -1; }
            if($a[0] < $b[0]){ return 1; }
            return 0;
        }); // Sort results by weight;
        
        return new \yii\data\ArrayDataProvider([
            'allModels' => $results,
            'pagination' => [
                'pageSize' => $this->resultsPerPage,
                'page' => $this->page - 1
            ]
        ]);
    }
}
