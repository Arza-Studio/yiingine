<?php 
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\modules\users\models\User;
use \yii\helpers\Url;

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><a href="<?= Url::to(['/'.$this->context->id.'/admin/user/index']); ?>"><?= Yii::tA($this->context->label); ?></a></h3>
    </div>
    <div class="panel-body">
        <?= User::find()->where(['status' => User::STATUS_ACTIVE])->count(); ?> active users<br/>
        <?= User::find()->where(['status' => User::STATUS_NOACTIVE])->count(); ?> inactive users<br/>
        <?= User::find()->where(['status' => User::STATUS_BANNED])->count(); ?> banned users<br/>
    </div>
</div>

