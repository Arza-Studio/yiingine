<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

/**
 * This is the configuration for generating message translations
 * for the Yii framework. It is used by the './yii message' command.
 */
return [
    'sourcePath' => '@yiingine',
    'messagePath' => '@yiingine/messages',
    'languages' => ['fr'],
    'overwrite' => true,
    'useYiingineCategories' => true,
    'except' => [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
        '.*',
        'htaccess',
        '/PhpMessageSource.php' // Contains a call to Yii::t() that is not for translation.
    ]
];
