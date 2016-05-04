<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
defined('YIINGINE_VERSION') or define('YIINGINE_VERSION', '2.0');

$config = isset($config) ? $config : require($applicationDirectory.'/config/web.php');

require($applicationDirectory.'/vendor/autoload.php');
require($applicationDirectory.'/vendor/yiisoft/yii2/BaseYii.php');

/**
 * Yii is a helper class serving common framework functionalities.
 *
 * It extends from [[\yii\BaseYii]] which provides the actual implementation.
 * By writing your own Yii class, you can customize some functionalities of [[\yii\BaseYii]].
 *
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> <antoine.mercier-linteau@arza-studio.com>
 * @since 2.0
 */
class Yii extends \yii\BaseYii
{
    /** Finds the correct string for a language in array('lang1' => 'string1', 'lang2' => 'string2').
     * @param array $translations the array that contains the translations.
     * @param array $params parameter to the translation.
     * @param string $language to force a specific language. If null, the application language
     * will be used.
     * @return string the translated string.*/
    public static function tA($translations, $params = [], $language = null)
    {
        if(!is_array($translations)) // If translations is not valid.
        {
            return $translations; // Do not translate.
        }
        
        $language = $language === null ? \Yii::$app->language: $language;
        $message = isset($translations[$language]) ? $translations[$language] : $translations[\Yii::$app->sourceLanguage];
        
        return self::t('none', $message, $params, null);
    }
}

// Register Yii's autoloader to load classes.
spl_autoload_register(['Yii', 'autoload'], true, true);

// Set properties required to autoload classes.
Yii::$classMap = require($applicationDirectory.'/vendor/yiisoft/yii2/classes.php');
Yii::$container = new yii\di\Container();
Yii::setAlias('@yiingine', __DIR__);

// Use a different configuration if we are in console mode.
$yiingineConfig = require(defined('CONSOLE') && CONSOLE ? __DIR__.'/config/console.php' : __DIR__.'/config/web.php');

// Merge the two configuration files.
/* Applications configuration takes precedence over yiingine configuration. That way, an application
* can override the yiingine's settings. This code drills down the config array to
* replace values and merge arrays.*/
$config = \yii\helpers\ArrayHelper::merge($yiingineConfig, $config);

/**
 * Dumps the content of a variable as a Yii Exception. If the DEBUG constant
 * is not set, the server will return a 500 error.
 * @param mixed $variable to dump
 * @throws \yii\base\Exception
 */
function dump($variable = '')
{
    // Dumps the content of $variable using Yii's developper error view.
    if(isset(Yii::$app->controller) && Yii::$app->controller instanceof APIController)
    {
        ob_get_clean(); // Discard what is in the output buffer.
        // Do not throw an exception, otherwise it gets displayed using HTML.
        echo yii\helpers\VarDumper::dumpAsString($variable, 10, false);
        die;
    }
    
    // Define a Dump class to have Dump appear on the error screen.
    // class Dump extends \yii\base\Exception {}
    
    throw new \yii\base\Exception(yii\helpers\VarDumper::dumpAsString(
        $variable, 
        10,
        false//!((defined('CONSOLE') ? CONSOLE : false) || YII_ENV == 'test' || (isset(Yii::$app->request->isAjaxRequest) ? Yii::$app->request->isAjaxRequest: false) || !YII_DEBUG || !isset(Yii::$app) || Yii::$app->response->format != \yii\web\Response::FORMAT_HTML)
    )); 
}

if(defined('CONSOLE') && CONSOLE) //If the yiingine is in console mode.
{
    return (new yiingine\console\Application($config))->run(); // Run the console application and return the exit code.
}
else
{
    define('CONSOLE', false); //We are not running in console mode.
    (new yiingine\web\Application($config))->run();
}
