<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$type = isset($type)? $type: 'success';

if($type == 'error')
{
    $type = 'danger'; // Convert error messages to bootstrap danger class.
}

$this->title = isset($title) ? $title: Yii::t(\yiingine\modules\users\controllers\RegisterController::className(), 'Registration');
$this->params['breadcrumbs'][] = $this->title;
?>

<h1><?php echo $this->title ?></h1>

<div class="alert alert-<?php echo $type; ?>">
    <?php echo $message; ?>
</div>
