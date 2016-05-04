<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$model = $this->module->getModuleModel();

$this->beginContent('//layouts/siteBody');
?>

<div class="corpus grid_12"><?php echo $this->adminOverlay($model, true); ?>
    <?php $this->renderPartial('engine.modules.media.views.layouts._content', array('content' => $content, 'model' => $model)); ?>
</div>

<?php $this->endContent();
