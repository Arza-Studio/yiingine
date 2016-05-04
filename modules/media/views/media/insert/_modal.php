<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$this->context->layout = $this->context->model->getTitle() ? '{header}{content}{footer}' : '{content}{footer}';

echo $this->context->view->render('@yiingine/modules/media/widgets/views/_modal.php');
