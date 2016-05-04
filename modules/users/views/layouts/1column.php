<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$this->beginContent('@app/views/layouts/main.php');

echo $this->render('@app/modules/media/views/media/page/1column.php', [
    'model' => $model = $this->context->module->getModuleModel(),
    'variables' => array_merge($this->context->runBeforeRender($model), ['module' => $content]),
    'layout' => '<div class="container"><div class="row"><div class="col-sm-12 corpus">{content}</div></div></div>'
]);

$this->endContent();
