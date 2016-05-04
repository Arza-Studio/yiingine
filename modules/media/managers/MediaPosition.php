<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
namespace yiingine\modules\media\managers;

/** 
 * Manages a CustomField of type mediaPosition. MediaPosition is a position field
 * adapted for use with the media module.
 * */
class MediaPosition extends \yiingine\modules\customFields\managers\Position
{        
    /** 
     * @inheritdoc 
     * */
    public function init()
    {
        parent::init();
        
        // Media are always grouped by type.
        $this->relatedAttribute = 'type';
        $this->actionParams = 'array("type" => $model->type)';
    }
    
    /** Sets the related attributes for particular model.
     * @param CustomizableModel the model to which the position field belongs */
    protected function setRelatedAttributes($model)
    {
        if(!($field = $this->getField())) // If no field is set.
        {
            return;
        }
        
        if($this->getField()->configuration) // If some extra grouping attributes were specified in configuration.
        {
            $grouping = eval('return '.$this->getField()->configuration.';');
            
            if(isset($grouping[$model->type])) // If there are grouping attributes for this type.
            {
                for($i = 0 ; $i < 2 ; $i++) // Only two extra grouping attributes are supported for the moment.
                {
                    if(isset($grouping[$model->type][$i]))
                    {
                        $this->{'relatedAttribute'.($i + 1)} = $grouping[$model->type][$i];
                    }
                }
            }
        }
    }
}
