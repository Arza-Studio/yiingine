<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

# HTML

// MESSAGE
if(!isset($message))
{
    if(in_array('ViewableInterface', class_implements($model)))
    {
        $message = Yii::t('yiingine\web\admin\ModelController', '{title} was sucessfully saved', ['title' => $model->getTitle()]);
    }
    else
    {
        $id = isset($model->primaryKey) ? $model->primaryKey : '' ;
        $message = Yii::t('yiingine\web\admin\ModelController', 'Record {id} was sucessfully saved', ['id' => $id]);
    }
}

$buttons = ['{returnToForm}' => '', '{viewInSite}' => ''];

// If the "return to form" button should be displayed.
if(!isset($noReturnToFormButton) || $noReturnToFormButton !== true)
{
    $buttons['{returnToForm}'] = \yii\helpers\Html::a('', Yii::$app->request->referrer, [
        'class'=>'fa fa-pencil',
        'title' => Yii::t(\yiingine\web\admin\ModelController::className(), 'Go back to previous form'),
    ]);
}

// If the "view in site" button should be displayed.
if(!isset($noViewInSiteButton) || $noViewInSiteButton !== true)
{
    if(in_array('ViewableInterface', class_implements($model)) && $model->getUrl())
    {
        $buttons['{viewInSite}'] = \yii\helpers\Html::a('', $model->getUrl(), [
            'class'=>'fa fa-dot-circle-o',
            'title' => Yii::t(\yiingine\web\admin\ModelController::className(), 'View in site'),
        ]);
    }
}

Yii::$app->session->addFlash(\yiingine\widgets\FlashMessage::SUCCESS, [
    'message' => $message,
    'template' => '{toggle}{close}{returnToForm}{viewInSite}',
    'buttons' => $buttons,
]);
