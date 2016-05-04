<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use rmrevin\yii\fontawesome\FA;

echo $this->render('_common', ['model' => $model]);

$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = Yii::t('generic', 'Update');

if(!Yii::$app->request->isAjax) // Do not waste time displaying buttons when doing ajax requests.
{
    // If the model can be viewed within the site.
    if($model instanceof \yiingine\db\ViewableInterface && $url = $model->getUrl())
    {
        //Add a "View button" button to the left buttons of the action bar.
        $this->params['leftButtons'][] = Html::a(FA::icon('dot-circle-o'), $url, [
            'class' => 'btn btn-primary',
            'title' => Yii::t('yiingine\web\admin\ModelController', 'View in site')
        ]);
    }
    
    // If the model can be copied.
    if(!$this->context->singleton && $this->context->allowCreate && $this->context->allowCopy && $this->context->checkAccess('create'))
    {      
        // Add a "copy" button to the left buttons.
        $this->params['leftButtons'][] = Html::a(FA::icon('copy'), ['create', 'copy' => $model->id], [
            'class' => 'btn btn-primary',
            'title' => Yii::t('yiingine\web\admin\ModelController', 'Create a new identical record')
        ]);
    }
}

echo $this->render('//admin/model/_form', ['model' => $model, 'form' => $form]); 
