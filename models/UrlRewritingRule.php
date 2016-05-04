<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\models;

use \Yii;
use \yii\web\UrlRule;

/**
 * This is the model class for table "url_rewriting)rules". A row in this table
 * represents an  url rewriting rule to be added dynamincally to CEngineUrlManager.
 * 
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class UrlRewritingRule extends \yiingine\db\ActiveRecord implements \yiingine\db\AdministrableInterface
{
    /** 
     * @var boolean if the pattern attribute can be empty. An empty pattern attribute
     * applies to the home page.
     * */
    public $allowEmptyPattern = true;
    
    /** 
     * @inheritdoc
     * */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'ActiveRecordOrderingBehavior' => '\yiingine\behaviors\ActiveRecordOrderingBehavior',
        ]);
    }
    
    /**
     * @inheritdoc
     * */
    public function __toString()
    { 
        return $this->pattern.($this->languages ? '('.$this->languages.')': '');
    }
    
    /**
    * @inheritdoc
    */
    public static function getModelLabel($plural = false)
    {
        return Yii::t(__CLASS__, '{n, plural, =1{URL rewriting rule}other{URL rewriting rules}}', ['n' => $plural ? 2 : 1]);
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName() { return 'url_rewriting_rules'; }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        /* NOTE: you should only define rules for those attributes that
         * will receive user inputs. */
        
        return [
            [['position', 'enabled'], 'default', 'value' => 1],
            ['encode_params', 'default', 'value' => 1],
            [['mode'], 'default', 'value' => 0],
            ['mode', 'in', 'range' => [0, UrlRule::PARSING_ONLY, UrlRule::CREATION_ONLY]],
            [array_merge($this->allowEmptyPattern ? []: ['pattern'], ['position', 'enabled', 'encode_params', 'mode', 'route']), 'required'],
            ['position', 'integer', 'integerOnly' => true, 'min' => 0],
            [['enabled', 'encode_params'], 'boolean'],
            ['system_generated', 'boolean', 'on' => 'search'],
            ['system_generated', 'default', 'value' => 0, 'on' => 'search'],
            [['defaults', 'pattern', 'route', 'languages'], 'string', 'max' => 255],
            [['suffix', 'verb'], 'string', 'max' => 31],
            ['defaults', 'validateEval'],
            ['languages', 'match', 'pattern' => '/^[a-zA-Z_]+$/u'],
            //array('pattern', 'match', 'pattern' => '/^[a-zA-Z0-9_\-%\:\<\\\>\+]+$/u'),
            //Rule uniqueness should be by language but this generates a bug: 'criteria' => 'languages!="'.$this->languages.'"'
            ['pattern', 'unique'],
        ];
    }
    
    /**
     * Validate attributes that should evaluate correctly.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateEval($attribute, $params)
    {
        if(!$this->$attribute) //If the attribute is empty.
        {
            return;
        }
        
        try
        {
            if(@eval('return '.$this->$attribute.';') === false) //Attempt to evalute the expression.
            {
                $this->addError($attribute, Yii::t(__CLASS__, 'Evaluation of {attr} fails.', array('{attr}' => $attribute)));
            }
            else if(!is_array(eval('return '.$this->$attribute.';'))) //The expession must be an array
            {
                $this->addError($attribute, Yii::t(__CLASS__, 'Expression must return array.'));
            }
        }
        catch (Exception $e) // If evaluation threw an exception.
        {
            Yii::t(__CLASS__, 'Evaluation of {attr} throws {exception} with message {message}.', array('{attr}' => $attribute, '{exception}' => get_class($e), '{message}' => $e->getMessage()));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'defaults' => Yii::t(__CLASS__, 'Default parameters'),
            'encode_params' => Yii::t(__CLASS__, 'Encode parameters'),
            'host' => Yii::t(__CLASS__, 'Host'),
            'mode' => Yii::t(__CLASS__, 'Mode'),
            'pattern' => Yii::t(__CLASS__, 'Pattern'),
            'route' => Yii::t(__CLASS__, 'Route'),
            'suffix' => Yii::t(__CLASS__, 'URL suffix'),
            'verb' => Yii::t(__CLASS__, 'Verb'),
            'enabled' => Yii::t(__CLASS__, 'Enabled'),
            'languages' => Yii::t(__CLASS__, 'Languages'),
            'position' => Yii::t(__CLASS__, 'Position'),
            'system_generated' => Yii::t(__CLASS__, 'System generated'),
        ]);
    }
    
    /**
     * @inheritdoc
     * */
    public function attributeDescriptions()
    {
        return array_merge(parent::attributeDescriptions(), [
            'defaults' => Yii::t(__CLASS__, 'The default GET parameters (name=>value) that this rule provides. When this rule is used to parse the incoming request, the values declared in this property will be injected into $_GET.'),
            'encode_params' => Yii::t(__CLASS__, 'If URL parameters should be encoded.'),
            'host' => Yii::t(__CLASS__, 'The pattern for the host.'),
            'mode' => Yii::t(__CLASS__, 'If the rule is for both creation and parsing of URLs or both.'),          
            'pattern' => Yii::t(__CLASS__, 'The regular expression used to parse and match URLs.'),
            'route' => Yii::t(__CLASS__, 'The controller/action pair.'),
            'suffix' => Yii::t(__CLASS__, 'The URL suffix used for this rule. For example, ".html" can be used so that the URL looks like pointing to a static HTML page. Leave empty to use the default suffix.'),
            'verb' => Yii::t(__CLASS__, 'The HTTP verb (e.g. GET, POST, DELETE) that this rule should match. If this rule can match multiple verbs, please separate them with commas. If this property is not set, the rule can match any verb. Note that this property is only used when parsing a request. It is ignored for URL creation.'),
            'enabled' => Yii::t(__CLASS__, 'Enables this rule.'),
            'languages' => Yii::t(__CLASS__, 'A comma separated list of the language codes this rule applies to. Leave empty if it applies to all languages.'),
            'position' => Yii::t(__CLASS__, 'The precedence of this rule.'),
            'system_generated' => Yii::t(__CLASS__, 'If this rule has been automatically generated.'),
        ]);
    }
        
    /** 
     * @inheritdoc 
     * */
    public function getAdminUrl()
    {
        return ['/admin/urlRewritingRule/update', 'id' => $this->id];
    }
    
    /** 
     * @inheritdoc
     * */
    public function isAccessible()
    {
        return Yii::app()->user->can($his->formName().'-view'); //Use normal access checking.    
    }
    
    /** 
     * @inheritdoc
     * */
    public function getEnabled() { return true; }
}
