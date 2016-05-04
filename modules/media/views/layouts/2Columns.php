<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$model = $this->module->getModuleModel();

$this->beginContent('//layouts/siteBody');
?>
<div class="corpus container_12"><?php echo $this->adminOverlay($model, true); ?>
    <div class="corpusColumn1 grid_8">
        <?php $this->renderPartial('engine.modules.media.views.layouts._content', array('content' => $content, 'model' => $model)); ?>
    </div>
    <div class="corpusColumn2 grid_4">
        <?php
        // Administrators are allowed to see all associated media.
        $relation = !Yii::app()->user->isGuest && UsersModule::user()->superuser ? 'all_associated_media' : 'associated_media';
        foreach($model->$relation as $child)
        {
            $this->renderPartial('app.modules.media.views.media.'.lcfirst(str_replace(' ', '', ucwords(mb_strtolower(str_replace('_', ' ', $child->type))))).'._column', array('model' => $child));
        }
        ?>
    </div>
</div>
<?php $this->endContent(); ?>
