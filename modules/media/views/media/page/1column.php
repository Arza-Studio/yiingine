<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

// Render the page default view without the {aside} part
echo $this->render('@app/modules/media/views/media/page/2columns.php', [
    'model' => $model,
    'layout' => '<div class="container"><div class="row"><div class="col-sm-12 corpus">{header}{content}</div></div></div>',
    'variables' => $variables
]);
