<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\themes\admin\base;

use \Yii;

/**
 * Base theme for the yiingine's admin.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 * */
class Theme extends \yiingine\base\Theme
{
    /**
     * @inheritdoc
     * */
    public $assetBundle = '\yiingine\themes\admin\base\Asset';
}

/**
 * AssetBundle for the Base admin theme.
 */
class Asset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/themes/admin/base/web';
    
    /** @inheritdoc */
    public $css = [
        'css/adminMenu.css',
        'css/form.css',
    ];
    
    public $depends = [
        'yiingine\assets\common\CommonAsset',
        'yiingine\assets\admin\AdminAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\bootstrap\BootstrapThemeAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
    ];
}
