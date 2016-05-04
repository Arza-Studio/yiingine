<?php 
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\models;

use \Yii;

/**
* A model class for submitting problem reports.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class ProblemReport extends \yii\base\Model implements \yiingine\db\DescriptiveInterface
{        
    /** @var string to hold the captcha submission.*/
    public $captcha; 
    
    /** @var string an email to which a copy of the report should be sent.*/
    public $email;
    
    /** @var string the date and time at which the report was generated.*/
    public $dateTime;
    
    /** @var string the user id of the user that generated the report. 0 if the user
     * was a guest.*/
    public $userId;
    
    /** @var string the url that triggered the error.*/
    public $url;
    
    /** @var string the referring url that triggered the error.*/
    public $referrer;
    
    /** @var integer the HTTP mthod that triggered the error.*/
    public $method;
    
    /** @var integer the HTTP error code of the error.*/
    public $code;
    
    /** @var string the error message.*/
    public $message;
    
    /** @var string the browser that triggered the error.*/
    public $browser;
    
    /** @var string the name of the application.*/
    public $application;
    
    /** @var string a description of the steps taken to trigger the error.*/
    public $description = '';
    
    /** @var boolean if a copy of the report should be sent to the reporter.*/
    public $copy = 0;
    
    /** @var boolean if the report has been sent already. Used to prevent sending of multiple reports.*/
    public $sent = 0;
    
    /** @var integer the height component of the screen's resolution.*/
    public $screenHeight = 0;
    
    /** @var integer the width component of the screen's resolution.*/
    public $screenWidth = 0;
    
    /** Initializes the model.*/
    public function init()
    {
        // Initializes system filled attributes.
        
        $this->dateTime = date(\yiingine\libs\Functions::$MySQLDateTimeFormat);
        $this->userId = Yii::$app->user->id;
        $this->browser = Yii::$app->request->getUserAgent();
        $this->application = Yii::$app->name;
        
        parent::init();
    }
    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        /* NOTE: you should only define rules for those attributes that
         * will receive user inputs.*/
        return [
            [['copy', 'sent'], 'default', 'value' => false],
            [['description', 'url', 'browser', 'copy', 'userId', 'sent', 'captcha'], 'required'],
            [['description'], 'string', 'min' => 10, 'max' => 10000],
            [['message'], 'string', 'min' => 0, 'max' => 1023],
            [['browser'], 'string', 'min' => 2, 'max' => 255],
            [['code', 'userId'], 'integer', 'min' => 0],
            [['screenHeight', 'screenWidth'], 'integer', 'min' => 0],
            [['url', 'referrer'], 'url', 'defaultScheme' => Yii::$app->request->isSecureConnection ? 'https': 'http', 'pattern' => '/^{schemes}:\/\/(?:(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)|\blocalhost\b)/i'],
            [['copy', 'sent'], 'boolean'],
            [['method'], 'in', 'range' => ['GET', 'PUT', 'POST', 'DELETE']],
            [['userId'], 'validateUserId'],
            // These parameters are filled automatically.
            // ['dateTime, application', 'unsafe'],
            [['captcha'], 'captcha', 'captchaAction' => 'site/Captcha.get'],
            [['email'], 'email'],
            [['email'], 'validateEmail']
        ];
    }
    
    /**
     * Validate the userId attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateUserId($attribute, $params)
    {
        if($this->$attribute === '0') // If the attribute is set to the user id for guests.
        {
            return; // Good to go.
        }
        // Else make sure it exists in the user table.
        CValidator::createValidator('exist', $this, array($attribute), array('attributeName' => 'id', 'className' => 'User'))->validate($this);
    }
    
    /**
     * Validate the email attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateEmail($attribute, $params)
    {
        if(!$this->copy) // If the user does not want a copy of the report.
        {
            return; // Good to go.
        }
        // Else make sure an email is present.
        CValidator::createValidator('required', $this, array($attribute))->validate($this);
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'dateTime' => Yii::t(__CLASS__, 'Date/Time'),
            'url' => 'URL',
            'referrer' => Yii::t(__CLASS__, 'URL Referrer'),
            'method' => Yii::t(__CLASS__, 'Method'),
            'browser' => Yii::t(__CLASS__, 'Browser'),
            'description' => Yii::t(__CLASS__, 'Description'),
            'copy' => Yii::t(__CLASS__, 'Send me a copy'),
            'code' => Yii::t(__CLASS__, 'Error code'),
            'message' => Yii::t(__CLASS__, 'Error message'),
            'userId' => Yii::t(__CLASS__, 'User ID'),
            'screenHeight' => Yii::t(__CLASS__, 'Screen height'),
            'screenWidth' => Yii::t(__CLASS__, 'Screen width')
        );
    }
    
    /**
     * @return array customized attribute descriptions (name=>label)
     */
    public function attributeDescriptions()
    {
        return array(
            'url' => Yii::t(__CLASS__, 'The URL that triggered the error.'),
            'referrer' => Yii::t(__CLASS__, 'The URL of the referring page.'),
            'method' => Yii::t(__CLASS__, 'The HTTP method of the request that triggered the error.'),
            'browser' => Yii::t(__CLASS__, 'The browser in use when the error occured.'),
            'description' => Yii::t(__CLASS__, 'A detailed description of the steps taken to trigger the error.'),
            'userId' => Yii::t(__CLASS__, 'The user ID logged id when the error occured. Set to 0 if it was a guest.')
        );
    }
    
    /** @param string $attribute the name of the attribute from which a description is needed.
     * @return string the description.*/
    public function getAttributeDescription($attribute)
    {
        $descriptions  = $this->attributeDescriptions();
         
        return isset($descriptions[$attribute]) ? $descriptions[$attribute] : '';
    }
}
