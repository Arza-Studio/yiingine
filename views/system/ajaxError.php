<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

/**
* Returns the text associated with an error code.
* @param integer $code the errot code.
* @return string the error text.
*/
function errorCodeToText( $code )
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
function errorCodeToName( $code )
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


echo Yii::t('error', 'Error').' '.$error['code'].' "'.errorCodeToName($error['code']).'"'."\n";
echo $error['message'];
