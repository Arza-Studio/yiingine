<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\searchEngine\models;

use \Yii;

/**
* A model class for submitting terms to the search engine.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class Query extends \yii\base\Model
{            
    /** @var string the search query. */
    public $query = '';
    
    /** @var string the search language.*/
    public $language;
    
    /** @var SearchEngine the searchEngine associated with this query. */
    public $searchEngine;
    
    /**
     * @inheritdoc
     * */
    public function __construct($config)
    {
        parent::__construct($config);
        $this->scenario = isset($config['scenario']) ? $config['scenario']: 'webSearch';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        /* NOTE: you should only define rules for those attributes that
         * will receive user inputs.*/
        $rules = [
            ['language', 'required'],
            ['query', 'string', 'min' => $this->searchEngine->minimumQueryLength, 'max' => 60],
            ['language', 'in', 'range' => Yii::$app->getParameter('app.available_languages')],
            // Prevent forward shlashes from being included in the query due to bug #1867.
            ['query', 'match', 'pattern' => '[/]', 'not' => true, 'on' => 'webSearch']
        ];
        
        if($this->searchEngine->minimumQueryLength)
        {
            $rules[] = ['query', 'required'];
        }
        
        return $rules;
    }
    
     /** Override of parent implementation to format the query differently for validation.
     * @param boolean $clearErrors whether to call {@link clearErrors} before performing validation
     * @return boolean whether the validation is successful without any error.
     */
    public function validate($attributes = null, $clearErrors = true)
    {
        $query = $this->query; // Save the query to restore it later.
        
        $formattedQuery = $this->formatQuery();
        
        // Run validation on the whole query first.
        if(!$result = parent::validate($attributes, $clearErrors))
        {
            return false;
        }
        
        if(count($formattedQuery) > 6) // If the formatted query contains too many words.
        {
            $this->addError('query', Yii::t(__CLASS__, 'Query contains too many words'));
        }
        
        // Run validation for each term of the query.
        foreach($formattedQuery as $i => $term)
        {
            $this->query = $term.
            $result = $result && parent::validate($attributes, $clearErrors);
        }
        
        // Run validation on the whole query.
        $this->query = implode(' ', $formattedQuery);
        $result = $result && parent::validate($attributes, $clearErrors);
        
        $this->query = $query; // Restore the query.
        
        return $result;
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'query' => Yii::t(__CLASS__, 'Query'),
            'language' => Yii::t(__CLASS__, 'Language'),
        );
    }
    
    /** Parse a query and returned is as formatted array.
     * @return array the formatted query.*/
    public function formatQuery()
    {
        $query = $this->query;
        
        $query = rtrim($query);
        $query = ltrim($query);
        
        $formattedQuery = [];
        
        // Need a regex here.
        
        // Parses the query to keep blocks in quote contiguous.
        /*$word = 0;
        $block = false;
        for($i = 0; $i < strlen($query); $i++)
        {
            switch($query[$i])
            {
                case '"':
                    if(!$block) // If this is the beginning of a block.
                    {
                        $word = $i + 1;
                    }
                    $block = !$block;
                break;
                case ' '; // If this is the beggining of a word.
                    if(!$block) // If we are currently not parsing a block.
                    {
                        $formattedQuery[] = substr($query, $word, $i - $word);
                        $word = $i + 1;
                    }
                break;
            }
            
            if($i === strlen($query) - 1) // If this is the end of the search term.
            {
                // Add the rest of the query as a term.
                $formattedQuery[] = substr($query, $word, $i - $word + 1);
            }
        }*/
        
        preg_match_all('/\"[^"]+"/', $query, $matches);
        
        // Searches for expressions between quotes.
        foreach($matches[0] as $match)
        {
            $formattedQuery[] = str_replace('"', '', $match);
            // Remove the expression from the query.
            $query = str_replace($match, '', $query);
        }
        
        // Remove multiple spaces. Done two times to account for as much as four extraneous spaces.
        $query = str_replace('  ', ' ', $query);
        $query = str_replace('  ', ' ', $query);
        $query = rtrim($query);
        $query = ltrim($query);
        
        if($query) // If there is something left to search.
        {
            $formattedQuery = array_merge(explode(' ', $query), $formattedQuery);
        }
        
        if(count($formattedQuery) > 1) // If there is more than one term to search.
        {
            // Also match the whole query.
            $formattedQuery[] = str_replace('"', '', $this->query);;
        }
        
        return $formattedQuery;
    }
}
