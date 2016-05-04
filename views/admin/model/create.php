<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

echo $this->render('_common', ['model' => $model]);

$this->params['breadcrumbs'][] = Yii::t('generic', 'Create');

echo $this->render('//admin/model/_form', ['model' => $model, 'form' => $form]);
