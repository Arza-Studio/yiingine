<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

use \yiingine\modules\customFields\models\ModelModel;
use \Yii;

/** Manages a CustomField of type ManyToManyField.
 * */
class ManyToMany extends OneToMany
{                                
    /** @var string the name of the table that stores the relations.*/
    private $_table = '';
    
    /** @param string $table the name of the table that contains the MANY_MANY relations.*/
    public function setTable($table)
    {
        $this->_table = $table;
    }
    
    /** 
     * @return string the name of the table that contains the MANY_MANY relations.
     * */
    public function getTable()
    {
        if(!$this->_table) //If no table is set.
        {
            throw new \yii\base\Exception('The name of that table that stores MANY_MANY relations has not been set!');
        }
        
        return $this->_table;
    }
    
    /**
     * @inheritdoc
     * */
    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            \yii\db\ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
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
         
        /* Create two relations based on this field, one for viewing the models
         * from within the admin (all models) and the other from within the site
         * (enabled models only).*/
        return [
            $this->getAttribute() => $this->owner->hasMany($modelClass, ['id' => 'child_id'])
                ->where(['enabled' => 1])
                ->orderBy($orderBy)
                ->viaTable($this->getTable(), ['parent_id' => 'id'], function($query){ $query->where(['relation_id' => $this->getField()->id])->orderBy('relation_position'); }),
            'all_'.$this->getAttribute() => $this->owner->hasMany($modelClass, ['id' => 'child_id'])
                ->orderBy($orderBy)
                ->viaTable($this->getTable(), ['parent_id' => 'id'], function($query){ $query->where(['relation_id' => $this->getField()->id])->orderBy('relation_position'); })
        ];
    }
    
    /** 
     * Save the related fields.
     * @param Event $event the event parameters.
     * */
    public function afterSave($event)
    {
        $field = $this->getField();
    
        // Save relations that were provided through the grid.
        if(isset($_POST[$this->owner->formName()][$field->name.'_related']))
        {
            // Get all the related ids.
            $ids = $_POST[$this->owner->formName()][$field->name.'_related'];
    
            if(!is_array($ids)) // Makes sure we are getting an array.
            {
                $ids = explode(',', $ids);
            }
    
            // Remove empty entries.
            foreach($ids as $key => $id) { if($id == '') { unset($ids[$key]); } }
    
            $ids = array_unique($ids); // Cannot have two relations to the same model.
    
            $newIds = [];
            foreach($ids as $id) // Transform ids because they are in the id:class form.
            {
                $id = explode(':', $id);
                $newIds[] = $id[0];
            }
            $ids = $newIds;
    
            ModelModel::$table = $this->getTable();
    
            // Loop through existing relations to modify them.
            foreach(ModelModel::find()->where(['relation_id' => $field->id, 'parent_id' => $this->owner->id])->all() as $rel)
            {
                // If this relation cannot be found within the related ids.
                if(!in_array($rel->child_id, $ids))
                {
                    $rel->delete(); // It has been deleted.
                    continue;
                }
    
                $key = array_search($rel->child_id, $ids);
    
                // If the position of the relation has changed.
                if($rel->relation_position != $key + 1)
                {
                    $rel->relation_position = $key + 1;
                    $rel->save();
                }
                // Else the relation has not changed.
    
                unset($ids[$key]); // The relation has been processed.
            }
    
            // The remaining $ids are new relations.
            foreach($ids as $position => $id)
            {
                $relation = new ModelModel();
                $relation->relation_id = $field->id;
                $relation->relation_position = $position + 1;
                $relation->parent_id = $this->owner->id;
                $relation->child_id = $id;
                $relation->save();
            }
        }
    }
    
    /** 
     * Delete all relations the model is part of.
     * @param Event $event the event parameters.
     * */
    public function beforeDelete($event)
    {
        ModelModel::$table = $this->getTable();
        
        // Delete all child relations.
        ModelModel::deleteAll(['parent_id' => $this->owner->id, 'relation_id' => $this->getField()->id]);
    
        // Delete all parent relations.
        ModelModel::deleteAll(['child_id' => $this->owner->id, 'relation_id' => $this->getField()->id]);
    }
    
    /** 
     * Triggered when a customizable model is cloned.
     * @param Event $event the cloning event. $event->owner is the clone.
     * */
    public function afterClone($event)
    {
        if(CONSOLE) // If the engine is in CONSOLE mode.
        {
            return; // Cloning relations does not work in CONSOLE mode.
        }
    
        if(Yii::$app->request->isPost) // If this is a post request.
        {
            return; // Relations have already been cloned.
        }
    
        $ids = [];
    
        // Insert each related model in the ids array.
        foreach($this->owner->{'all_'.$this->getAttribute()} as $related)
        {
            $ids[] = $related->id.':'.$related::className();
        }
    
        // Feed the related models to the related GridView through the $_GET special variable.
        $_GET[$this->getAttribute().'-related'] = implode(',', $ids);
    }
}
