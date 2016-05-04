<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

use \Yii;

/** Manages a CustomField of type OneToMany.
 * 
 * NOTE: If a customizable model uses many fields like this who refer to the same model, edit conflicts
 * will arise because the field whose models are saved last will erase the modifications made by previous
 * fields.
 * */
class OneToMany extends BaseRelational
{    
    /** 
     * @inheritdoc
     * */
    public function actions()
    {    
        return [
            $this->getAttribute().'.search' => array_merge(\yiingine\modules\customFields\widgets\RelatedGridView::actions()['search'], 
                ['manager' => $this]
            )
        ];
    }
    
    /** 
     * @inheritdoc
     * */
    public function rules()
    {
        return array_merge(parent::rules(), [[$this->getAttribute(), '\yiingine\modules\customFields\managers\OneToManyValidator', 'manager' => $this]]);
    }
    
    /** 
     * @inheritdoc
     * */
    protected function renderInputInternal()
    {
        return [
            'type' => '\yiingine\modules\customFields\widgets\RelatedGridView',
            'configuration' => $this->getField()->getConfigurationArray(),
            'availableClasses' => $this->getAvailableModelClasses(),
            'relatedModels' => $this->getRelatedModels()
        ];
    }
    
    /**
     * @return array(adminUrl => modelInstance) the classes that can be created and associated.
     * */
    public function getAvailableModelClasses()
    {
        $configuration = $this->getField()->getConfigurationArray();
        
        $classes = [];
        
        $manager = $this; // Required by the eval.
        
        // Convert the associatableModelClasses array to the new format.
        foreach(eval('return '.$configuration['associatableModelClasses'].';') as $adminUrl => $model)
        {
            if(!is_array($model))
            {
                $model = [
                    'adminUrl' => $adminUrl,
                    'model' => $model        
                ];
            }
            
            $classes[] = $model;
        }
        
        return $classes;
    }
    
    /** 
     * Get the list of related models from $_POST or $_GET.
     * @return array the list of related models.
     * */
    public function getRelatedModels()
    {
        $name = $this->getAttribute();
        
        /* An input that will contain the related ids. This input will be
         * used by the behavior to retrieve the new list of related models. */
        $related = []; //The list of related models.
        $relatedInputId = $name.'-related';
        $currentRelated = $this->owner->{'all_'.$name}; // The currently related models.
        
        /* If extra relations were provided through a POST or GET request. This is implemented
         * to allow the user to add relations from the gridview.*/
        if(isset($_GET[$relatedInputId]) || isset($_POST[$this->owner->formName()][$relatedInputId]))
        {
            // Get all the related ids.
            $ids = explode(',', Yii::$app->request->isPost ? $_POST[$this->owner->formName()][$relatedInputId]: $_GET[$relatedInputId]);
            
            foreach($ids as $id) // Iterate though each provided id.
            {
                if(!$id) { continue; } //Skip if id is empty.
                $id = explode(':', $id); // id is id:class. 
                if(count($id) !== 2) // Invalid $id format.
                { 
                    throw new \yii\base\Exception('Invalid ID format!');
                } 
                $class = $id[1];
                $id = $id[0];
                
                foreach($currentRelated as $r) // If the model is already part of the related models.
                {
                    if($r->id == $id)
                    {
                        $related[] = $r;
                        continue 2;
                    }
                }
                
                if($id == $this->owner->id) // A model cannot be related to itself.
                {
                    throw new \yii\base\Exception('Forbidden or illegal related object id!');
                }
                
                if(!$m = $class::findOne($id)) // If the new model could not be found.
                {
                    /* The related model has not been found within the available models!
                     * This condition will occur if someone is trying to force an illegal relation.*/
                    throw new \yii\base\Exception('Forbidden or illegal related object id!');
                }
                
                // Should check if the id is a permitted model.
                
                $related[] = $m; //Add it to the relation.
            }
        }
        else
        {
            return $currentRelated; // No new added models.
        }
        
        return $related;
    }
    
    /**
     * @inheritdoc
     * */
    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            \yii\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
            \yiingine\db\ActiveRecord::EVENT_AFTER_CLONE => 'afterClone'
        ];
    }
    
    /**
     * @inheritdoc
     * */
    protected function getRelations()
    {
        $field = $this->getField();
    
        $configuration = $field->getConfigurationArray();
        $modelClass = $configuration['modelClass'];
         
        $orderBy = (new $modelClass())->hasAttribute('position') ? 'position': '';
         
        if(!isset($configuration['attribute']))
        {
            $configuration['attribute'] = 'parent_id';
        }
         
        /* Create two relations based on this field, one for viewing the models
         * from within the admin (all models) and the other from within the site
         * (enabled models only).*/
        return [
            $field->name => $this->owner->hasMany(
                    $modelClass,
                    ['id' => $configuration['attribute']]
                    )->where(['enabled' => 1])->orderBy($orderBy),
            'all_'.$field->name => $this->owner->hasMany(
                    $modelClass,
                    ['id' => $configuration['attribute']]
                    )->orderBy($orderBy),
        ];
    }
    
    /** Validates that if the association is required, related models were provided.
     *  @param $event Event the event parameters.*/
    public function beforeValidate($event)
    {
        $field = $this->getManager()->getField();
        $model = $event->sender;
    
        if($field->required && // The field is required.
                Yii::app()->request->isPostRequest && // The request was a POST.
                isset($_POST[get_class($model)]) && // Data was POSTed to this model.
                (!isset($_POST[get_class($model)][$field->name.'_related']) || // The data did not contain this field.
                        trim($_POST[get_class($model)][$field->name.'-_elated']) == '') // The attribute is empty.
                )
        {
            /* Since this is a related field, the model does not have an attribute with this name
             so the required validator neccessarily causes an error. By adding it selectivily, the
             correct behavior is recreated. */
            //$rules[] = array($field->name, 'required');
            $model->addError($field->name, Yii::t('yii', '{attribute} cannot be blank.', array('{attribute}' => $event->sender->getAttributeLabel($field->name ))));
        }
    }
    
    /** Save the related fields.
     *  @param Event $event the event parameters.*/
    public function afterSave($event)
    {
        $field = $this->getManager()->getField();
    
        //Save relations that were provided through the grid.
        if(isset($_POST[$this->owner->formName()][$field->name.'_related']))
        {
            //Get all the related ids.
            $ids = explode(',', $_POST[$this->owner->formName()][$field->name.'_related']);
    
            //Remove empty entries.
            foreach($ids as $key => $id) { if($id == '') { unset($ids[$key]); } }
    
            $ids = array_unique($ids); //Cannot have two relations to the same model.
    
            $configuration = $field->getConfigurationArray();
            $model = $configuration['modelClass'];
    
            /*
             * NOTE: If a customizable model uses many fields like this who refer to the same model, edit conflicts
             * will arise because the field whose models are saved last will erase the modifications made by previous
             * fields.
             */
    
            //Set all related models' foreign key to 0.
            $model::model()->updateAllWithEvents(array($configuration['attribute'] => 0), $configuration['attribute'].'='.$this->owner->getPrimaryKey());
    
            $newIds = array();
            foreach($ids as $id) // Transform ids because they are in the id:class form.
            {
                $id = explode(':', $id);
                $newIds[] = $id[0];
            }
            $ids = $newIds;
    
            if(!empty($ids)) //If there are models to update.
            {
                $params = array($configuration['attribute'] => $this->owner->getPrimaryKey());
    
                //If the model has a column named position.
                if($model::model()->getTableSchema()->getColumn('position'))
                {
                    //Update each model individually because we need to set their position.
                    foreach(array_values($ids) as $position => $id)
                    {
                        $params['position'] = $position + 1;
                        $model::model()->updateByPk($id, $params);
                    }
                }
                else //Update all models at once.
                {
                    //Update the models that are still part of the relation or that have been added.
                    $model::model()->updateByPk(array_values($ids), $params);
                }
            }
    
            // If the related model has the ActiveRecordCachingBehavior.
            if($model::model()->getBehavior('ActiveRecordCachingBehavior'))
            {
                $model::model()->ActiveRecordCachingBehavior->flushCache();
            }
        }
    }
    
    /** Set all the foreign key field in the related model to 0.
     *  @param Event $event the event parameters.*/
    public function afterDelete($event)
    {
        $field = $this->getManager()->getField();
    
        $configuration = $field->getConfigurationArray();
    
        $model = $configuration['modelClass'];
        $model::model()->updateAllWithEvents(array($configuration['attribute'] => 0), $configuration['attribute'].'='.$this->owner->getPrimaryKey());
    
        // If the related model has the ActiveRecordCachingBehavior.
        if($model::model()->getBehavior('ActiveRecordCachingBehavior'))
        {
            $model::model()->ActiveRecordCachingBehavior->flushCache();
        }
    }
    
    /** Triggered when a customizable model is cloned.
     * @param Event $event the cloning event. $event->owner is the clone.*/
    public function afterClone($event)
    {
        if(CONSOLE) // If the engine is in CONSOLE mode.
        {
            return; // Cloning relations does not work in CONSOLE mode.
        }
    
        if(Yii::app()->request->isPostRequest) // If this is a post request.
        {
            return; // Relations have already been cloned.
        }
    
        $ids = array();
        $fieldName = $this->getManager()->getField()->name;
    
        // Insert each related model in the ids array.
        foreach($this->owner->{'all_'.$fieldName} as $related)
        {
            $ids[] = $related->id.':'.get_class($related);
        }
    
        // Feed the related models to the related CGridView through the $_GET special variable.
        $_GET[$fieldName.'-related'] = implode(',', $ids);
    }
}

/**
 * A validator for validating one to many fields.
 * */
class OneToManyValidator extends \yii\validators\Validator
{
    /** @var OneToMany the manager for the field to validate.*/
    public $manager;
    
    /**
     * @inheritdoc
     * */
    public function validateAttribute($model, $attribute)
    {
        $relatedModels = $this->manager->getRelatedModels($model);
        
        $relatedInputId = $this->manager->getAttribute().'-related';
        
        //Check if we have not passed any limits for the related models.
        foreach($this->manager->getAvailableModelClasses() as $param)
        {
            // If the max parameter is not set or set to infinity.
            if(!isset($param['max']) || $param['max'] == 0)
            {
                $param['max'] = 999999999999999999999999999;
            }
        
            // If the min parameter is not set or set to 0.
            if(!isset($param['min']) || $param['min'] < 1)
            {
                $param['min'] = 0;
            }
        
            $number = 0;
            foreach($relatedModels as $model) // Count the number of models.
            {
                /* Models from the media module are treated a bit differently, its dirty
                 * and it introduces coupling but for a lack of a better way of doing this
                 * it works.*/
                if($param['model'] instanceof Medium && $model instanceof Medium)
                {
                    if($model->type == $param['model']->type)
                    {
                        $number++;
                    }
                }
                else if($param['model'] instanceof $model) // If the model is not a medium.
                {
                    $number++;
                }
            }
        
            if($number > $param['max']) // If there are too many of those models.
            {
                $model->addError($attribute, Yii::t(__CLASS__, 'Too many associated {model}, the maximum is {max}', array('{model}' => $param['model']->getDescriptor(), '{max}' => $param['max'])));
            }
            else if($number < $param['min']) // If there are not enough of those models.
            {
                $model->addError($attribute, Yii::t(__CLASS__, 'Not enough associated {model}, the minimum is {min}', array('{model}' => $param['model']->getDescriptor(), '{min}' => $param['min'])));
            }
        }
    }
}
