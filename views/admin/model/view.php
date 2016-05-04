<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

// Register a script that makes all input elements read only.
$this->registerJs('$("#'.$model->formName().'-form").find("input, textarea").each(function(){$(this).attr("readonly", "readonly");});', \yii\web\View::POS_READY);

// Reuse the update view.
echo $this->render('//admin/model/update', ['model' => $model, 'form' => $form]);

array_replace($this->params['breadcrumbs'], [1 => Yii::t('generic', 'View')]);
