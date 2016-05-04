<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

# PAGE BACKGROUND
if(isset($model->background) && $model->background)
{
     Yii::app()->setParams(array('background' => $model->getManager('background')->getFileUrl($model)));
}

$variables = $this->runBeforeRender($model);
?>

<?php 
# TITLE
$title = $model->getTitle(true);
if(!$model->getEnabled())
{
    $title = str_replace('</h1>','<span style="color:orange;"> ('.Yii::t('generic', 'Disabled').')</span></h1>', $title);
}

echo $title;

// If the module has content to add to the layout.
if($moduleContent = $model->page_content)
{
    /* Replaces references to render variables in the content by the actual
     * value of those variables. A reference is written "{{$name}}".*/
    foreach($variables as $name => $value)
    {
        $moduleContent = str_replace('{{$'.$name.'}}', $value, $moduleContent);
    }

    // Replace the {{$module}} palce holder with the module's data.
    echo str_replace(array('<p>{{$module}}</p>', '{{$module}}'), $content, $moduleContent);
}
else
{
    echo $content;
}

$this->runAfterRender($model);
