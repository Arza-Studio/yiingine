<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\web\View;
use \yii\helpers\Html;
use \yii\helpers\Url;
use \yii\widgets\Pjax;

$name = $attribute; // The name of the field.

$relatedModel = $configuration['modelClass'];
$relatedModel = new $relatedModel();

// Register the css file for the grid view.
$cssFile = \yiingine\assets\admin\AdminAsset::register($this)->baseUrl.'/css/model/_adminGridView.css';
$this->registerCssFile($cssFile);

$relatedInput = [];
$related = $relatedModels;

// Get all models from the relation.
foreach($related as $r)
{
    /* NOTE: ID is provided in this format to allow models from different tables to be
     * aggregated under the same relation (future support).*/
    $relatedInput[] = $r->getPrimaryKey().':'.get_class($r);
}

// GRID
// Register a script that rebuilds the related list.
$this->registerJs('
    function rebuildRelatedList(table, list)
    {
        var ids = "";
        $(table).find(".idCell").each(function(){
            ids += $(this).html() + ",";
        });
        ids = ids.substr(0, ids.length - 1); //Remove the last comma.
        $(list).attr("value", ids);
    }
', View::POS_BEGIN);

Pjax::begin(['id' => $name.'-grid', 'timeout' => false, 'linkSelector' => false, 'formSelector' => false]);
echo \yii\grid\GridView::widget([
    'id' => $name.'-list',
    'options' => ['class'=>'grid-view oneToManyGridView row','style'=>'padding:0 0 8px 0;border-bottom-style:dotted;'],
    'dataProvider' => new \yii\data\ArrayDataProvider(['allModels' => $related]),
    'filterModel' => $relatedModel,
    'columns' => array_merge($this->context->getColumns(), $this->context->getButtons($model, $this)),
]);
Pjax::end();

// ASSOCIATION FIELDS
# Add an existing object :
// Label
$addAvailable = Html::label(Yii::t(get_class($this->context), 'Associate an existing object'), $this->context->id, ['style' => 'display:block;margin:0px 0 1px 0;font-weight:normal;font-style:italic;line-height:15px;']);
// Autocomplete field.
$addAvailable .= Html::textInput($name.'-available', Yii::t(get_class($this->context), 'Choose an existing object'), ['style' => 'vertical-align:top;margin:3px 5px 0 0;width:200px;']);
// Hidden field to contain the autocomplete value.
$addAvailable .= Html::hiddenInput($name.'-value', '');
// Add related button
$addAvailable .= Html::tag('span', '', ['id' => 'addAvailable_'.$name, 'class'  => 'btnFa fa fa-plus disabled', 'title' => Yii::t(get_class($this->context), 'Associate an existing object')]);
// Row div warpping
$addAvailable = Html::tag('div', $addAvailable, ['style' => 'width:278px;float:left;border-right:1px dotted;padding:2px 0 2px 0;']);
// Javascript : Register a script that adds related models when the + button is pressed.
//\yiisoft\yii2-jui\AutoCompleteAsset::register($this);
$this->registerJs('
    $("[name='.$name.'-available]").autocomplete({
        source: function(request, response)
        {
            // Since the engine uses "query" to refer to the search string, the search needs to be rewritten.
            $.get("'.Url::to([$name.'.search']).'", { query: request.term }, response);
        },
        minLength: 0,
        autoFocus: true,
        messages: 
        { 
            noResults: "'.Yii::t(get_class($this->context), 'No available objects').'", 
            results: function(results)
            { 
                if(results > 1)
                {
                    return results + " '.Yii::t(get_class($this->context), 'objects found').'";
                }

                return results + " '.Yii::t(get_class($this->context), 'object found').'";
            }
        },
        select: function(event, ui) 
        {
            $("[name='.$name.'-value]").val(ui.item.value);
            $("[name='.$name.'-available]").val(ui.item.label);
            $("#addAvailable_'.$name.'").removeClass("disabled");
            event.preventDefault();
        },
        response: function(event, ui) // Remove already selected items.
        {
            var ids = $("[name=\"'.Html::getInputName($model, $name.'_related').'\"]").prop("value").split(",");
            
            ids.push("'.$model->id.':'.get_class($model).'"); // A model cannot be related to itself.
            
            for(var i = 0; i < ui.content.length;)
            {
                if($.inArray(ui.content[i]["value"], ids) >= 0)
                {
                    ui.content.splice(i, 1);
                }
                else
                {
                    i++;
                }
            }
        
            if(ui.content.length == 0) // If there are no available models.
            {
                
            }
        },
        search: function(event, ui)
        { 
            $("#addAvailable_'.$name.'").addClass("disabled");
            $("#'.$name.'-value").val("");
        }
    });
    
    $("[name='.$name.'-available]").click(function()
    {
        // If the default string is present.
        if($(this).val() == "'.Yii::t(get_class($this->context), 'Choose an existing object').'")
        {
            $(this).val("");
        }
        
        if($(this).val() == "") // If the input is empty.
        {
            $(this).autocomplete("search", ""); // Displays all results.
        }
    });  
    
    $("#addAvailable_'.$name.'").click(function()
    {
        if($("[name='.$name.'-value]").val() == "") // If no value has been selected.
        {
            return;
        }
        
        var ids = $("[name=\"'.Html::getInputName($model, $name.'_related').'\"]").prop("value");
        ids += (ids.length ? "," : "") + $("[name='.$name.'-value]").val();
        $("[name=\"'.Html::getInputName($model, $name.'_related').'\"]").attr("value", ids);
        $.pjax.defaults.timeout = false;
        $.pjax.reload({container: "#'.$name.'-grid", "url": "'.Url::to().'&'.$name.'-related=" + ids, push: false});
        $("[name='.$name.'-available]").val("");
    });        
', View::POS_READY);

# Create and associate an new object :
if(Yii::$app->controller->getSide() === \yiingine\web\Controller::ADMIN && $availableClasses) // If there is available classes to create
{
    if(!Yii::$app->request->isAjax) // If this is not an ajax request.
    {
        // Add a drop down to select the type of object being created.
        
        // Label
        $addNewHtml = Html::label(Yii::t(get_class($this->context), 'Create and associate a new object'), $this->context->id, ['style' => 'display:block;margin:-2px 0 1px 0;font-weight:normal;font-style:italic;']);
        
        $data = []; // Will be used to populate the drop down list.
        foreach($availableClasses as $class)
        {
            if(!$class['model']->isAccessible()) // If the user cannot access this model.
            {
                continue; // Skip it.
            }
            
            // If a model of this type cannot be created.
            if(isset($class['create']) && !$class['create'])
            {
                continue; // Skipt it.
            }
            
            $data[Url::to($class['adminUrl'])] = $class['model']->getDescriptor();
        }
        
        if(count($data) > 1) // If several classes are available.
        {
            $addNewHtml .= Html::dropDownList($name.'-classes', null, $data, ['prompt' => Yii::t(get_class($this->context), 'Choose an object type'),'style' => 'vertical-align:top;margin:3px 5px 0 0;width:200px;']);
            $disabled = ' disabled';
        }
        else // If only one class is available, disable the dropdown.
        {
            $addNewHtml .= Html::dropDownList($name.'-classes', null, $data, ['style'=>'vertical-align:top;margin:3px 5px 0 0;width:200px;', 'disabled' => 'disabled']);
            $disabled = '';
        }
        
        // Add new button
        $addNewHtml .= Html::tag('span', '', ['id' => 'addNew_'.$name, 'class'  => 'btnFa fa fa-plus'.$disabled, 'title' => Yii::t(get_class($this->context), 'Create and associate a new object')]);
        // Javascript : Register a script that adds new models when the + button is pressed.
        $this->registerJs('
            // Disable the add button if no value is selected.
            $("[name='.$name.'-classes]").change(function()
            {
                $(this).val() == "" ? $("#addNew_'.$name.'").addClass("disabled") : $("#addNew_'.$name.'").removeClass("disabled") ;
            });
            $("#addNew_'.$name.'").click(function(e)
            {
                if($("#addNew_'.$name.'").hasClass("disabled"))
                {
                    return;
                }
                // Set and open editor.
                e.preventDefault();
                var options = new Array(); 
                options["title"] = "'.Yii::t(get_class($this->context), 'Create and associate').' : " + $("[name='.$name.'-classes]").find(":selected").html();
                options["submitButtonLabel"] = "'.Yii::t('generic', 'Create').'";
                var url =  $("[name='.$name.'-classes]").find(":selected").val();
                var editor = new AjaxModelEditor(url, function(data)
                {
                    var ids = $("[name=\"'.Html::getInputName($model, $name.'_related').'\"]").prop("value");
                    ids += (ids.length ? "," : "") + data["id"] + ":" + data["class"];
                    $("[name=\"'.Html::getInputName($model, $name.'_related').'\"]").attr("value", ids);
                    $("#'.$name.'-list").yiiGridView("update", {data: {"'.$name.'-related": ids}});
                }, options);
                editor.open();
            });        
        ', View::POS_READY);
    }
    else
    {
        $addNewHtml = Html::label(Yii::t(get_class($this->context), 'New object creation is not available in this window.'), $this->context->id, ['style' => 'display:block;margin:0px 8px 1px 0;font-weight:normal;font-style:italic;color:gray;line-height:15px;']);
    }
    
    $addNewHtml = Html::tag('div', $addNewHtml, ['style' => 'width:276px;float:left;padding:2px 0 2px 8px;']);
}
else
{
    $addNewHtml = '';
}

echo Html::tag('div', $addAvailable.$addNewHtml.Html::tag('div', '', ['style' => 'clear:left;']), ['class' => 'row associationFields', 'style'=>'padding:6px 0 6px 0;margin:0 0 8px 0;border-bottom-style:dotted;']);

// AJAX MODEL EDITOR (Only availble in the admin).
// echo Yii::$app->controller->getSide() === \yiingine\components\GUIController::ADMIN ? \yiingine\widgets\admin\AjaxModelEditor::widget(): '';

// HIDDEN FIELD
echo Html::hiddenInput(Html::getInputName($model, $name.'_related'), implode(',', $relatedInput));
