<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$this->context->layout = $this->context->model->getTitle() ? '{header}{description}{footer}' : '{description}{footer}';
$this->context->descriptionTruncate = 500;

echo $this->context->view->render('@yiingine/modules/media/widgets/views/_thumbnail.php');
