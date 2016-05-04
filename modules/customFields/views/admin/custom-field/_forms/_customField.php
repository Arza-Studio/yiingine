<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Url;

$manager = false;

if($model->formName() !== 'CustomField') // If a type is defined for this model.
{
    $manager = $model->getModule()->factory->createManager($model);
}

// Get the inputs for the parameters.
$parameters = [];
foreach($this->context->module->getFieldParameters() as $name => $param)
{
    $parameters[$name] = $param->render($model);
}

if($model->isNewRecord)
{
    $types = array_flip($this->context->module->factory->getTypes());
}
else
{
    $types = [];
    
    // Some types, such as relations or those handling files, cannot be changed to.
    foreach($this->context->module->factory->getTypes() as $name => $type)
    {
        // Fake an existing record.
        $type = new $type($model->getModule(), ['id' => 4]);
        $type->isNewRecord = false;
        if($type->isAttributeSafe('type')) // If the type can be changed.
        {
            $types[$type::className()] = $name;
        }
    }
}

/*// Register code mirror assets.
// For usage, see http://codemirror.net/doc/manual.html
$url = Yii::$app->assetManager->publish(Yii::getAlias('@yiingine/vendor/codemirror'))[1];
$this->registerJsFile($url.'/lib/codemirror.js');
$this->registerCssFile($url.'/lib/codemirror.css');
// The PHP mode depends on the c-like mode.
$this->registerJsFile($url.'/mode/clike/clike.js');
$this->registerJsFile($url.'/mode/php/php.js');
$this->registerCssFile($url.'/theme/mbo.css');*/

\yiingine\assets\admin\CodeMirrorAsset::register(Yii::$app->view);

// Fix a bug where the z-index of code mirror is greater than 0.
$this->registerCss('.CodeMirror{z-index: 0;width:570px;height:200px;}');

/* Register a script to change the form according to the type selected and
* modify the submit button action.*/
$this->registerJs('
    function initCustomFieldForm()
    {
        $("#typeDropDown").change(function(){
            $.post("'.($model->isNewRecord ? Url::to(['create']): Url::to(['update', 'id' => $model->primaryKey])).'", $("form").serialize() , function(data)
            {
                $(".form").replaceWith(data);
                $("#'.$model->formName().'-submit").attr("onclick", "window.onbeforeunload = null; $(\'#" + $(".form").children("form").attr("id")+ "\').submit();");
                initCustomFieldForm();
            });
        });  
        
        if($("#CustomField_configuration").length)
        {
            var cMGroupTitle_configuration = $("#CustomField_configuration").parent().prev();
            var cMGroupTitleClosing_configuration = false ;
            if(cMGroupTitle_configuration.find(".openCloseBtn").hasClass("fa-plus-circle"))
            {
                cMGroupTitle_configuration.trigger("click");
                cMGroupTitleClosing_configuration = true;
            }
            var codeMirror_configuration = CodeMirror.fromTextArea($("#CustomField_configuration")[0],
            {
                theme: "mbo",
                lineNumbers: true,
                indentUnit: 4,
            });
            
            codeMirror_configuration.on("change", function(instance, changeObj){ 
                // CodeMirror.save() does not work.
                $("#CustomField_configuration").html(instance.getValue()); 
            });
        
            if(cMGroupTitleClosing_configuration)
            {
                cMGroupTitle_configuration.trigger("click");
            }
        }
    }
       
    initCustomFieldForm();
', \yii\web\View::POS_READY);


// Build the available groups list.
$formGroups = [0 => Yii::t('generic', 'None')];
foreach(\yiingine\modules\customFields\models\FormGroup::find()->where(['owner' => $this->context->module->tableName])->all() as $group)
{
    $formGroups[$group->id] = $group->name.' ('.$group->position.')';
}
asort($formGroups);

return [
    'title' => $model->getModelLabel(),
    'type' => 'fieldset',
    'elements' => [
        [
            'type' => 'group',
            'title' => Yii::t(\yiingine\modules\customFields\controllers\admin\CustomFieldController::className(), 'IDENTIFIERS'),
            'elements' => [
                'name' => [
                    'type' => 'text',
                    'size' => 20,
                    'maxlength' => 50,
                ],
                'title' => [
                    'type' => 'text',
                    'size' => 60,
                    'maxlength' => 255,
                    'translatable' => true
                ],
                'description' => [
                    'type' => 'textarea',
                    'style' => 'width:99%;',
                    'rows' => 4,
                    'translatable' => true
                ]
            ]
        ],
        [
            'type' => 'group',
            'title' => Yii::t(\yiingine\modules\customFields\controllers\admin\CustomFieldController::className(), 'CONFIGURATION'),
            'elements' => [
                'type' => [
                    'type' => 'dropdownlist',
                    'id' => 'typeDropDown',
                    'items' => $types,
                    'prompt' => Yii::t('generic', 'Select an item'),
                    'forceDisplay' => true // Display event if unsafe.
                ],
                'form_group_id' => [
                    'type' => 'dropdownlist',
                    'items' => $formGroups,
                ],
                'size' => [
                    'type' => 'text',
                    'size' => 10,
                    'maxlength' => 10,
                    'visible' => $manager,
                ],
                'min_size' => [
                    'type' => 'text',
                    'size' => 10,
                    'maxlength' => 10,
                    'visible' => $manager,
                ],
                'required' => [
                    'type' => 'checkbox',
                    'visible' => $manager,
                ],
                'configuration' => [
                    'type' => 'textarea',
                    'visible' => $manager,
                    'id' => 'CustomField_configuration'              
                ],
                'validator' => [
                    'type' => 'text',
                    'size' => 60,
                    'maxlength' => 255,
                    'visible' => $manager,
                ],
                'default' => [
                    'type' => 'textarea',
                    'style' => 'width:99%;',
                    'rows' => 4,
                    'visible' => $manager,
                    'translatable' => true,
                ],
                'in_forms' => [
                    'type' => 'checkbox',
                    'visible' => $manager,
                ],
                'translatable' => [
                    'type' => 'checkbox',
                    'visible' => $manager,
                ],
                'protected' => [
                    'type' => 'checkbox',
                    'visible' => $model->isNewRecord,
                ],
                'position' => [
                    'type' => '\yiingine\widgets\admin\PositionManager',
                    'model' => $model,
                    'attribute' => 'position',
                    'relatedAttribute' => 'form_group_id',
                    'relatedValue' => $model->form_group_id,
                    'visible' => $manager,
                ],
            ]
        ],
        [
            'type' => 'group',
            'title' => Yii::t(\yiingine\modules\customFields\controllers\admin\CustomFieldController::className(), 'SPECIAL'),
            'elements' => $parameters
        ],
    ]
];
