<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\searchEngine;

use \Yii;

/**
 * SearchEngine module class.
 */
class Module extends \yiingine\base\Module
{        
    /** 
     * @inheritdoc
     * */
    public function init()
    {
        $this->label = Yii::t(__CLASS__, 'Search Engine');
        $this->moduleMapRoute = false; // No sitemaps for this module.
        
        parent::init();
    }
}
