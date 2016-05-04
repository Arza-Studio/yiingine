<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
use \yii\helpers\Url;
use \yii\helpers\Html; 

echo Html::beginTag('div', ['class' => 'input-group']);

echo Html::activeTextInput($this->context->model, $this->context->attribute, [
    'maxlength' => $this->context->maxLength,
    //'style' => 'width:40px;text-align:center;margin:0 5px 0 0;',
    'class' => 'form-control'
]);

$id = $this->context->id;
$inputId = Html::getInputId($this->context->model, $this->context->attribute);

$arguments = [
    'attribute' => $this->context->attribute,
    'isNewRecord' => $this->context->model->isNewRecord ? 1 : 0 
];

$onClick = '
    $("#'.$id.'-loader'.'").css("display", "inline-block");
    $("#'.$inputId.'").attr("value", "");
    var arguments = '.\yii\helpers\Json::encode($arguments).';
';

foreach(['', '1', '2'] as $i)
{
    if($attribute = $this->context->{'relatedAttribute'.$i})
    {
        $onClick .= 'arguments["relatedAttribute'.$i.'"] = "'.$attribute.'";';
        
        // If a related value is provided by the model.
        if(($value = $this->context->{'relatedValue'.$i}) !== '')
        {
            $onClick .= 'arguments["relatedValue'.$i.'"] = "'.$value.'";';
        }
        else // The value is in the form.
        {
            $onClick .= 'arguments["relatedValue'.$i.'"] = $("#'.Html::getInputId($this->context->model, $attribute).'").val();';
        }
    }
}

$onClick .= '$.get("'.Url::to(array_merge(['positionManager.getLastAvailableValue'], $this->context->actionParams ? $this->context->actionParams : [])).'", arguments, function(data){
    $("#'.$inputId.'").attr("value", data);
    $("#'.$id.'-loader'.'").css("display", "none");
});';

/* If the model is a new record and has no related attribute for its value field, it
 * is fetched right away. If it has a related attribute, this is not done because that
 * related attribute is most likely not set.*/
if($this->context->model->isNewRecord && !$this->context->relatedAttribute)
{
    $this->registerJs('
        $("#'.$id.'-loader'.'").css("display", "inline-block");
        $.get("'.Url::to(array_merge(['positionManager.getLastAvailableValue'], $this->context->actionParams ? $this->context->actionParams : [])).'",
            {
                attribute: "'.$this->context->attribute.'",
                isNewRecord: true
            },
            function(data)
            {
                $("#'.$inputId.'").attr("value", data);
                $("#'.$id.'-loader'.'").css("display", "none");
            }
        );
    ', \yii\web\View::POS_READY);
}

echo Html::Tag('span', '', ['id' => $id.'-loader', 'class' => 'smallLoader']);

echo Html::tag('span', Html::button(Yii::t(get_class($this->context), 'Last'), [
    'class' => 'btn btn-primary', 
    'onclick' => $onClick,
]), ['class' => 'input-group-btn']);

echo Html::tag('span', Html::button(Yii::t(get_class($this->context), 'First'), [
    'class' => 'btn btn-primary',
    'onclick' => '$("#'.$inputId.'").attr("value", 1);',
]), ['class' => 'input-group-btn']);

echo Html::endTag('div');
