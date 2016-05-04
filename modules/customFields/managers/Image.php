<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;


/** 
 * Manages a CustomField of type image.
 * */
class Image extends File
{        
    /**
     * @inheritdoc
     * */
    public $extensions = ['jpg', 'jpeg', 'png', 'gif'];
}
