<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\assets\admin;

/**
 * CodeMirror asset bundle for the yiingine.
 */
class CodeMirrorAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@bower/codemirror';
    
    /** @inheritdoc */
    public $css = [
        'lib/codemirror.css',
        'theme/mbo.css'
    ];
    
    /** @inheritdoc */
    public $js = [
        'lib/codemirror.js',
        'mode/clike/clike.js',
        'mode/php/php.js'
    ];
}
