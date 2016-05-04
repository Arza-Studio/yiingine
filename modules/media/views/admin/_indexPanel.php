<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Url;
use \yii\helpers\Html;
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><a href="<?= Url::to(['/'.$this->context->id.'/admin/index/index']); ?>"><?= Yii::tA($this->context->label); ?></a></h3>
    </div>
    <div class="panel-body">
        <ul class="list-group">
            <?php foreach($this->context->mediaClasses as $class): ?>
                <li class="list-group-item">
                    <span class="badge"><?= $count = $class::find()->count(); ?></span>
                     <?= Html::a($class::getModelLabel($count), Url::to(['/'.$this->context->id.'/admin/'.lcfirst($class::shortClassName()).'/index'])); ?><br/>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

