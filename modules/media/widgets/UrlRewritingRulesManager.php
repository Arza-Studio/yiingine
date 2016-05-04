<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

/**
 * A widget that allows a medium to define its own url rewriting rules.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class UrlRewritingRulesManager extends CWidget
{
    /** @var CActiveRecord the owner model.*/
    public $model;
    
    /** @var CAssociatedMenuItemsField the field for which this widget was created.*/
    public $field;
    
    /** @var string the attribute of the model that owns the rules. Not used by this widget. */
    public $attribute;
    
    /** @var string the language for which rules will be created. */
    public $language;
    
    /**
    * Executes the widget.
    * This method is called by {@link CBaseController::endWidget}.
    */
    public function run() 
    {                       
        Yii::import('engine.models.admin.UrlRewritingRule');
        
        $dbRules = $this->model->url_rewritingBehavior->getRules($this->language); // Get the rules already stored in database.
        
        $rules = array();
         //Rules that were deleted during the last POST request. Used only if validation failed.
        $deletedRules = array();
        
         //If data was posted to the page, create models for rules that were added.
        if(isset($_POST['UrlRewritingRules']))
        {
            foreach($_POST['UrlRewritingRules'] as $attributes)
            {
                //If this widget manage only rules for one language and this rule does not apply to this language.
                if(isset($attributes['languages']) && $this->language && ($attributes['languages'] != $this->language))
                {
                    continue; //Skip it.
                }
                
                if(isset($attributes['id']) && isset($dbRules[$attributes['id']])) //If the rule exists in the database.
                {
                    $rule = $dbRules[$attributes['id']]; //Add it to the existing rules. 
                }
                else //This is a new rule.
                {
                    $rule = new UrlRewritingRule();
                    
                    if($this->model->type != 'MODULE')
                    {
                        $rule->route = 'media/default/index';
                        $rule->defaults = 'array("id"=>4)'; //Use a phony id just for validation.
                    }
                    else //The MODULE type uses a specia route.
                    {
                        $rule->route = $this->model->module_owner_id;
                    }
                }
                
                $rule->allowEmptyPattern = false; //Disallow empty patterns for rules.
                
                $rule->attributes = $attributes;
                
                if($attributes['delete'])
                {
                    $deletedRules[] = $rule;
                }
                else
                {
                    $rules[] = $rule;
                }
                
                $rule->validate(); //Run validation to display errors.
            }
        }
        else
        {
            $rules = $dbRules; //The rules are those fetched from the database.
        }
        
        $this->render('urlRewritingRulesManager', array(
            'model' => $this->model,
            'rules' => $rules,
            'deletedRules' => $deletedRules
        ));
    }
}
