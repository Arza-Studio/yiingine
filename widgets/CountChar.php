<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \Yii;

/**
 * CountChar adds in form fields label a characters counter with a configurable limit for
 * inputs with a certain CSS class.
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class CountChar extends \yii\base\Widget
{    
    /** @var string containing the css selector of the form field */
    public $inputSelector = '';
    
    /** @var integer for the maximum characters limitation advisable */
    public $warningLimit = 0;
    
    /** @var integer for the strict maximum characters limitation wanted */
    public $errorLimit = 0;
    
    /** @var string two modes available :
     *  "additional" : increase from 0 (default)
     *  "substractive" : decrease from the limit value
     *  */
    public $mode = 'additional';
    
    /** @var boolean : does not allow to enter characters beyond the limit (errorLimit then warningLimit) */
    public $locked = false;

    /**
    * @inheritdoc
    */
    public function run()
    {
        $this->view->registerJs("
        function countChar(inputId, warningLimit, errorLimit, mode, locked, value)
        {    
            // If a limitation has been set
            if(warningLimit!=0 || errorLimit!=0)
            {
                // Locking (with errorLimit only)
                if(errorLimit!=0 && locked)
                {
                    value = value.substring(0, errorLimit);
                    $('#'+inputId).val(value);
                }
                
                var length = value.length;
                var charNumObject = $('#'+inputId+'CharNum'); //console.log(charNumObject);
                
                
                // Subtractive Mode
                if(mode=='substractive')
                {
                    limit = (errorLimit!=0) ? errorLimit : warningLimit ;
                
                    if(Math.abs(limit - length))
                
                    charNumObject.text(' (' + (limit - length) + ' ' + (Math.abs(limit - length) == 1 ? '".Yii::t(__CLASS__, 'character')."' : '".Yii::t(__CLASS__, 'characters')."') + ')');
                }
                // Additional
                else 
                {
                    charNumObject.text(' (' + length + ' ' + (Math.abs(length) == 1 ? '".Yii::t(__CLASS__, 'character')."' : '".Yii::t(__CLASS__, 'characters')."') + ')');
                }

                // Warning and error limit stylization
                if(errorLimit!=0 && length>=errorLimit)
                {
                    charNumObject.css('color','".Yii::$app->adminPalette->get('AdminError')."');
                }
                else if(warningLimit!=0 && length>=warningLimit)
                {
                    charNumObject.css('color','".Yii::$app->adminPalette->get('AdminWarning', 50)."');
                }
                else
                {
                    charNumObject.css('color','inherit');
                }
            }
        };
        
        function initCharNum(inputSelector, warningLimit, errorLimit, mode, locked)
        {
            //console.log('iniCharNum('+inputSelector+', '+errorLimit+', '+warningLimit+', '+mode+', '+locked+') !');
           
            $(inputSelector).each(function()
            {
                   // Adding span in field label
                var inputId = this.id; //console.log(inputId);
                   $('label[for=\"'+inputId+'\"]').filter(':last').append('<span id=\"'+inputId+'CharNum\"></span>');
           
                   // Init keyup event
                   $(this).bind('keyup',function(){ countChar(inputId, warningLimit, errorLimit, mode, locked, $('#' + inputId).val()); });
           
                   // Display the first countChar
                   val = $('#'+inputId).val();
                if(val)
                {
                    countChar(inputId, warningLimit, errorLimit, mode, locked, val);
                }          
            });
        }", \yii\web\View::POS_HEAD);
        
        $this->view->registerJs(
            'initCharNum("#'.$this->inputSelector.'", '.$this->warningLimit.' , '.$this->errorLimit.' , "'.$this->mode.'", '.($this->locked ? 'true' : 'false').');',
        \yii\web\View::POS_READY);    
    }
}
