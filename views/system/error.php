<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

/* @var $exception \yii\web\HttpException|\Exception */
/* @var $handler \yii\web\ErrorHandler */

if ($exception instanceof \yii\web\HttpException) 
{
    $code = $exception->statusCode;
} 
else 
{
    $code = 0;
}

$handler = Yii::$app->errorHandler;

$name = $handler->getExceptionName($exception);

/**
* Returns the text associated with an error code.
* @param integer $code the errot code.
* @return string the error text.
*/
function errorCodeToText($code)
{
    switch($code)
    {
        case 400:
            return Yii::t('error', 'The request could not be understood by the server due to malformed syntax. Please do not repeat the request without modifications.');
        case 403:
            return Yii::t('error', 'You do not have the proper credential to access this page.');
        case 404:
            return Yii::t('error', 'The requested URL was not found on this server. If you entered the URL manually please check your spelling and try again.');
        case 405:
            return Yii::t('error', 'The resource requested exists but it does no support the method provided.');
        case 500:
            return Yii::t('error', 'An internal error occurred while the Web server was processing your request.');
        case 501:
            return Yii::t('error', 'The system did not recognize the request method, or it lacks the ability to fulfill the request');
        case 503:
            return Yii::t('error', 'Our system is currently under maintenance. Please come back later.');
        default:
            return Yii::t('error', 'The above error occurred when the Web server was processing your request');
    }
}

/**
 * Returns the name associated with an error code.
 * @param integer $code the errot code.
 * @return string the error name.
 */
function errorCodeToName($code)
{
    switch($code)
    {
        case 400:
            return Yii::t('error', 'Bad request');
        case 401:
            return Yii::t('error', 'Unauthorized');
        case 403:
            return Yii::t('error', 'Forbidden');
        case 404:
            return Yii::t('error', 'Not found');
        case 405:
            return Yii::t('error', 'Method not allowed');
        case 500:
            return Yii::t('error', 'Internal server error');
        case 501:
            return Yii::t('error', 'Not implemented');
        case 503:
            return Yii::t('error', 'Service unavailable');
        default:
            return '';
    }
}

$this->title = Yii::t('error', 'Error');

$this->params['breadcrumbs'][] = $this->title;

if($exception instanceof \yii\base\UserException)
{
    $message = $exception->getMessage();
} 
else 
{
    $message = 'An internal server error occurred.';
}
?>

<div class="container" style="padding-top:50px; padding-bottom:50px">
    <h1 class="text-danger"><?= errorCodeToName($code).' (#'.$code.')' ?></h1>
    <h2><?= nl2br($handler->htmlEncode($message)) ?></h2>
    <p>
        <?= nl2br(errorCodeToText($code)); ?>
    </p>
    <?php 
        if(Yii::$app->getParameter('yiingine.error_reporting.enabled', '1') === '1' && ( // If error reporting is enabled.
            $code != 404 || // If the error was not a 404.
            Yii::$app->request->referrer !== null // If the error was caused by clicking on a link.
        ))
        {
            $url = [
                '/site/problem-report',
                /* Note: The reason why / is changed to {{ is that for security reason, it is not a 
                 * good idea to pass forward slashes in urls. See
                 * http://stackoverflow.com/questions/3235219/urlencoded-forward-slash-is-breaking-url
                 * for more information.*/                    
                'url' => urlencode(Yii::$app->request->url),
                'method' => Yii::$app->request->method,
                'code' => $code,
                /* Note: The reason why / is changed to {{ is that for security reason, it is not a 
                 * good idea to pass forward slashes in urls. See
                 * http://stackoverflow.com/questions/3235219/urlencoded-forward-slash-is-breaking-url
                 * for more information.*/    
                'message' => urlencode($message)
            ];
            
            if(Yii::$app->request->referrer) // If this page was accessed by clicking a link.
            {
                $args['referrer'] =  urlencode(Yii::$app->request->referrer);
            }
            
            echo '<div class="alert alert-info">';
            echo Yii::t('error', 'If the error was not expected, you have the possibility to {report} it.', [
                'report' => \yii\helpers\Html::a(Yii::t('error', 'report'), \yii\helpers\Url::to($url), ['id' => 'errorReport' ,'class' => 'noAjax', 'target' => '_blank'])
            ]);
            echo '</div>';
            
            // Register a piece of javascript code that will dynamically fill the screenHeight and screenWidth arguments.
            $this->registerJs('$("#errorReport").attr("href", $("#errorReport").attr("href") + "/screenHeight/" + window.screen.availHeight + "/screenWidth/" + window.screen.availWidth)', \yii\web\View::POS_READY);
        }
    ?>
    <h6><?= date('Y-m-d H:i:s', time()) ?></h6>
</div>
