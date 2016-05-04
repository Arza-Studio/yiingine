<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\grid;

use \Yii;

/**
 * Display information on associated models.
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class AssociatedStatsColumn extends \yii\grid\Column
{
    /** @var array the relations information should be gathered from. Leave empty to let the 
     * column guess.*/
    public $relations = [];
    
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        parent::init();
        
        if(!isset($this->options['style'])) // Use a default style if none is set.
        {
            $this->options['style'] = 'color:gray;font-size:11px;line-height:14px;display:block;font-style:italic;font-weight:normal;';
        }
    }
    
    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $string = '';
        
        $relations = $this->relations;
        
        if(!$relations) // If no relations were provided.
        {
            foreach($model->behaviors() as $behavior)
            {
                // If this is a behavior for a relation.
                if($behavior instanceof \yiingine\modules\customFields\behaviors\RelationalFieldBehavior)
                {
                    $relations[] = $behavior->getField()->name;
                }
            }
        }
        
        // Iterate through each related object.
        foreach($relations as $i => $relation)
        {                
            if($i > 0)
            {
                $string .= ', ';
            }
            
            /* If a relation with "all_" + the name of the field is defined, use it instead because
             * calling the normal relation will exclude the disabled models.*/
            $related = isset($model->{'all_'.$relation}) ? $model->{'all_'.$relation} : $model->{$relation};
            
            $string .= count($related).' '.$model->getAttributeLabel($relation);
        }
        
        return \yii\helpers\Html::tag('span', $string, ['style' => $this->options['style']]);
    }
}
