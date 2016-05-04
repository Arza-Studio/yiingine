<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \yii\helpers\Html;
use \Yii;

/**
 * A subclass of Yii's ActiveForm to adapt it to use with the Yiingine.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class ActiveForm extends \yii\bootstrap\ActiveForm
{    
    /** @var boolean warn the user if he is about to quit a form with unsaved changes.*/
    public $warnOnUnsavedChanges = true;
    
    /**
     * @inheritdoc
     * */
    public $fieldClass = 'yiingine\widgets\ActiveField';
    
    
    
    /** 
     * Display a Yii 1.1 style form structure.
     * @param \yii\base\Model $model the model this form structure is for.
     * @param array $structure the form structure.
     * @param string $name the name of the structure.
     * @return string the rendering result.
     * */
    public function formStructure($model, $structure, $name = false)
    {
        if(is_string($structure))
        {
            return $structure; // Nothing to do.
        }
        
        if(isset($structure['visible']) && !$structure['visible'])
        {
            return ''; // The element is not visible.
        }
        unset($structure['visible']);
        
        $result = ''; // The form markup.
        
        if(!isset($structure['type']))
        {
            $type = 'fieldset';
        }
        else
        {
            $type = $structure['type'];
            unset($structure['type']);
        }
        
        $advanced = isset($structure['advanced']) ? $structure['advanced'] : false;
        
        switch($type)
        {
            case 'fieldset':
                ///TODO if there are multiple fieldsets, only the last one is displayed.
                $result .= Html::beginTag('fieldset', ['class' => 'well bs-component']);
                if(isset($structure['title']))
                {
                    $result .= Html::tag('legend', $structure['title'], ['style' => 'margin: 0px']);
                }
                break;
            case 'group':
                $collapsible = isset($structure['collapsible']) ? $structure['collapsible']: true;
                $collapsed = isset($structure['collapsed']) && $collapsible ? $structure['collapsed']: false;
                
                $result .= Html::beginTag('div', ['class' => 'well group'.($advanced ? ' advanced' : ''), 'style' => 'cursor: pointer;']);
                $result .= Html::beginTag('legend', ['class' => 'group-title', 'onclick' => $collapsible ? 'toggleNodeVisibility(this)': '', 'style' => 'margin-bottom: 0px']);
                $result .= (isset($structure['title']) ? $structure['title']: '').'&nbsp;';
                $result .= Html::tag('span', '', ['class' => $collapsible ? 'open-close-btn'.($collapsed ? ' fa fa-plus-circle' : ' fa fa-minus-circle') : '']);
                $result .= Html::endTag('legend');
                
                break;
            case 'form':
                break;          
            default:
                if(!$model->isAttributeSafe($name))
                {
                    if(isset($structure['forceDisplay']) && $structure['forceDisplay'])
                    {
                        $structure['disabled'] = true;
                    }
                    else
                    {
                        break; // Unsafe attributes are not displayed.
                    }
                }
                
                $field = $this->field($model, $name);
                
                if(isset($structure['label'])) // If a custom label has been defined.
                {
                    $field->label($structure['label']);
                    unset($structure['label']);
                }
                else
                {
                    $field->label($model->isAttributeRequired($name) ? $model->getAttributeLabel($name).'<span class="required-field-marker">*</span>': null);
                }
                
                if(isset($structure['description'])) // If a custom description has been set.
                {
                    $field->hint($structure['description'], ['class' => 'help-block', 'tag' => 'span']);
                    unset($structure['description']);
                }
                else if(isset($structure['hint'])) // If a custom hint has been set.
                {
                    $field->hint($structure['hint'], ['class' => 'help-block', 'tag' => 'span']);
                    unset($structure['hint']);
                }
                else if($model instanceof \yiingine\db\DescriptiveInterface)
                {
                    $field->hint($model->getAttributeDescription($name), ['class' => 'help-block', 'tag' => 'span']);
                }
                
                // If the type is a widget class.
                if((strpos($type, '\\') !== false) || (strpos($type, '@') !== false) || (lcfirst($type) != $type))
                {
                    $field->widget($type, $structure);
                }
                else
                {
                    if(isset($structure['items'])) // If an item list was passed in the structure.
                    {
                        // It is an argument to the method that generates the field.
                        $items = $structure['items'];
                        unset($structure['items']);
                    }
                    
                    switch(strtolower($type)) // Some types are handled in a specific manner.
                    {
                        case 'checkbox': $field->checkbox($structure); break;
                        case 'checkboxlist': $field->checkboxList($items, $structure); break;
                        case 'radio': $field->radio($structure); break;
                        case 'radiolist': $field->radiolist($items, $structure); break;
                        case 'listbox': $field->listBox($items, $structure); break;
                        case 'text': $field->textInput($structure); break;
                        case 'textarea': $field->textarea($structure); break;
                        case 'password': $field->passwordInput($structure); break;
                        case 'hidden': 
                            $field->template = '{input}';
                            $field->hiddenInput($structure); 
                            break;
                        case 'dropdownlist': $field->dropDownList($items, $structure); break;
                        default: $field->input($type, $structure); break;    
                    }
                }
                
                return $field->render();
        }
        
        if(isset($structure['elements']))
        {
            foreach($structure['elements'] as $name => $element)
            {
                $result .= $this->formStructure($model, $element, $name);
            }
        }
        
        switch($type)
        {
            case 'fieldset':
                
                if(isset($structure['buttons']))
                {
                    foreach($structure['buttons'] as $name => $button)
                    {
                        $result .= Html::beginTag('div', ['class' => 'form-group']);
                        $result .= Html::submitButton($button['label'], ['class' => isset($button['class']) ? $button['class']: 'btn']);
                        $result .= Html::endTag('div');
                    }
                }
                
                $result .= Html::endTag('fieldset');
                break;
            case 'group':
                $result .= Html::endTag('div');
                break;
        }
        
        return $result;
    }
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        if($this->warnOnUnsavedChanges) // Must be after the render call because the activeForm widget is needed.
        {
            // Registers a script that informs the user he is about to navigate away from a page he has modified.
            $this->view->registerJs('var inputsChanged = false;', \yii\web\View::POS_BEGIN);
            
            $message = Yii::t(__CLASS__, 'Changes have not been saved!');
            $widgetId = $this->id;
            
            $this->view->registerJs(<<<JS
                $('.groupTitle, .groupTitle .openCloseBtn').mouseup(function()
                {
                    if(!$(this).find('.openCloseBtn'))
                    {
                        toggleNodeVisibility($(this).find('.openCloseBtn'));
                    }
                });
                
                // Initialise form gorups to their closed state.
                $('.openCloseBtn').each(function(){ 
                    // Close
                    if($(this).hasClass('fa-plus-circle'))
                    {
                        if($(this).parent().hasClass('groupTitle'))
                        {
                            $(this).closest('.group').children('.form-group').hide();
                        }
                        else // Child title.
                        {
                            $(this).closest('.form-group').find('div.blockForm').hide(); 
                        }
                    }
                });
                    
                // If a user has changed an input, he will be presented with a confirmation dialog if he tries to navigate away from the page.
                $(window).bind("beforeunload", function(event){
                    if(inputsChanged)
                    {
                        inputsChanged = false;
                        $(".loaderElement").hide();
                        return "$message";
                    }
                });
                // Do not display the alert on form submit.
                \$("form").submit(function(){inputsChanged = false;});
                    
                // NOTE: INPUT EVENT ONLY WORKS IN HTML5!
                //If an input has changed register it.
                \$("#$widgetId").find("input").bind('change input', function(){inputsChanged = true;})
                //If a textarea has changed register it.
                \$("#$widgetId").find("textarea").bind('change input', function(){inputsChanged = true;})
                //If a select has changed register it.
                \$("#$widgetId").find("select").bind('change input', function(){inputsChanged = true;})
JS
            , \yii\web\View::POS_READY);
        }
        
        $this->view->registerJs("
            function toggleNodeVisibility(node)
            {
                // On title click
                if($(node).hasClass('group-title'))
                {
                    toggleNodeVisibility($(node).find('.open-close-btn'));
                }
                // On open-close-btn click
                else
                {
                    // Close
                    if($(node).hasClass('fa-minus-circle'))
                    {
                        if($(node).parent().hasClass('group-title'))
                        {
                            $(node).closest('.group').children('.form-group').hide();
                        }
                        else // Child title.
                        {
                            $(node).closest('.form-group').find('div.blockForm').hide(); 
                        }
                        $(node).removeClass('fa-minus-circle').addClass('fa-plus-circle');
                    }
                    // Open
                    else
                    {
                        if($(node).parent().hasClass('group-title'))
                        {
                            $(node).closest('.group').children('.form-group').show();
                        }
                        else //Child title.
                        {
                            $(node).closest('.form-group').find('div.blockForm').show();
                        }
                        $(node).removeClass('fa-plus-circle').addClass('fa-minus-circle');
                    }
                    // Re-Initialize admin content
                    if(typeof initContent != 'undefined')
                    {
                        initContent();
                    }
                }
            }
        ", \yii\web\View::POS_HEAD);
        
        $this->view->registerCss('form .required-field-marker { color: red;}');
        
        return parent::run();
    }
}
