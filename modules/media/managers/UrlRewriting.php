<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\managers;

/** 
 * Manages a CustomField of type urlRewriting.
 * */
class UrlRewriting extends \yiingine\modules\customFields\managers\Base
{                                
    /** @var array the url rewriting rules to be saved.*/
    private $_rules = [];
    
    /** @var array the url rewriting rules to be deleted.*/
    private $_deletedRules = [];
    
    /**
     * @inheritdoc
     * */
    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            \yii\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            \yii\db\ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            \yiingine\db\ActiveRecord::EVENT_AFTER_CLONE => 'afterClone'
        ];
    }
    
    /**
     * @inheritdoc
     * */
    public function rules()
    {
        return []; // No validation rules.
    }
    
    /**
     * @inheritdoc
     */
    protected function renderInputInternal()
    {        
        return array(
            'type' => 'engine.modules.media.components.UrlRewritingRulesManager',
            'field' => $this->getField(),
            'translatable' => $this->getField()->translatable
        );
    }
    
    /** 
     * Validate the url rewriting rules.
     *  @param $event Event the event parameters.
     *  */
    public function beforeSave($event)
    {
        Yii::import('engine.models.admin.UrlRewritingRule');
    
        $validated = true;
    
        //If data was posted to the page, also create models for rules that were added.
        if(isset($_POST['UrlRewritingRules']))
        {
            $dbRules = $this->getRules(); // GET the rules already stored in database.
             
            $error = ''; //The last validation error.
                
            foreach($_POST['UrlRewritingRules'] as $attributes)
            {
                if(isset($attributes['id']) && isset($dbRules[$attributes['id']])) //If the rule exists in the database.
                {
                    $rule = $dbRules[$attributes['id']]; //Use an existing rule.
                }
                else //This is a new rule.
                {
                    $rule = new UrlRewritingRule();
                    $rule->system_generated = '1'; // Rule has been generated programmatically.
    
                    if($this->owner->type != 'MODULE')
                    {
                        $rule->route = 'media/default/index';
                        $rule->default_params = 'array("id"=>4)'; //Use a phony id just for validation.
                    }
                    else //The MODULE type uses a specia route.
                    {
                        $rule->route = $this->owner->module_owner_id;
                    }
                }
    
                $rule->allowEmptyPattern = false; //Disallow empty patterns for rules.
    
                $rule->attributes = $attributes;
    
                if($attributes['delete'])
                {
                    $this->_deletedRules[] = $rule;
                    continue; // Do not validate a deleted rule.
                }
                else
                {
                    $this->_rules[] = $rule;
                }
    
                if(!$rule->validate()) //If the rule failed to validate.
                {
                    $error = $rule->getErrors();
                    $error = current(array_pop($error));
                    $validated = false; //Model will fail validation.
                }
            }
        }
    
        if(!$validated) //If there were validation errors.
        {
            $this->owner->addError($this->getManager()->getField()->name, 'Url rewriting validation error: '.$error);
            $event->isValid = false; //Abort the saving process.
        }
    }
    
    /** 
     * Save url rewriting rules. The lists holding these rules will only be populated
     * and modified if a POST request was made to the server.
     *  @param $event Event the event parameters.
     *  */
    public function afterSave($event)
    {
        //Delete all deleted rules.
        foreach($this->_deletedRules as $rule)
        {
            if($rule->isNewRecord) //If the rule was never created in the first place.
            {
                continue; //Skip it
            }
            $rule->delete();
        }
    
        // The position of the first rule.
        $startPosition = 9999999999999;
    
        // Sort all rules by language.
        $rules = [];
        foreach($this->_rules as $rule)
        {
            $rules[$rule->languages][] = $rule;
    
            if($rule->position < $startPosition)
            {
                $startPosition = $rule->position;
            }
        }
    
        // Save all rules.
        foreach($rules as $language)
        {
            foreach($language as $position => $rule)
            {
                $rule->position = $startPosition + $position;
    
                // If the rule is a new rule.
                if($rule->isNewRecord && (!$this->owner->hasAttribute('module_owner_id') || !$this->owner->module_owner_id))
                {
                    $rule->default_params = '["id"=>'.$this->owner->id.']';
                }
                $rule->save(false); // Do not rerun validation.
            }
    
            $startPosition += count($language);
        }
    }
    
    /** 
     * Delete all url rewriting rules that concern this model.
     *  @param $event Event the event parameters.
     *  */
    public function beforeDelete($event)
    {
        Yii::import('engine.models.admin.UrlRewritingRule');
        UrlRewritingRule::model()->deleteAllByAttributes(array('route' => 'media/default/index', 'default_params' => 'array("id"=>'.$this->owner->id.')'));
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
    
        if(Yii::app()->request->isPostRequest) // If this is a post request.
        {
            return; // Relations have already been cloned.
        }
    
        $rules = array();
    
        foreach($this->getRules() as $rule)
        {
            $rules[uniqid()] = array(
                'id' => '',
                'delete' => '',
                'languages' => $rule->languages,
                'pattern' => $rule->pattern
            );
        }
    
        $_POST['UrlRewritingRules'] = $rules;
    }
    
    /**
     * @param string $language the language of rules wanted.
     * @return array the id => UrlRewritingRules associated with this model.
     * */
    public function getRules($language = null)
    {
        if($this->owner->isNewRecord)
        {
            return array(); // No rules for new models.
        }
         
        $rules = array();
         
        if($this->owner->type != 'MODULE')
        {
            $query = array('route' => 'media/default/index', 'default_params' => 'array("id"=>'.$this->owner->id.')');
        }
        else //The MODULE type uses a special route.
        {
            $query = array('route' => $this->owner->module_owner_id);
        }
    
        if($language) // If a language was specified.
        {
            $query['languages'] = $language;
        }
    
        //Add all rules fetched from the database.
        foreach(UrlRewritingRule::model()->findAllByAttributes($query) as $rule)
        {
            $rules[$rule->id] = $rule;
        }
         
        return $rules;
    }
}
