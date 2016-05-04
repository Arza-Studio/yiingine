<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\models\admin;

use \Yii;
use \yiingine\widgets\admin\FileListUploader;

/**
* A model class for managing the site's configuration.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class SiteConfiguration extends \yii\base\Model implements \yiingine\db\DescriptiveInterface
{                
    /** @var array the configuration entries managed by this model.*/
    public $configEntries;
    
    /** @var array the entries that were not found in the database. These entries will
     * not appear on the form. */
    public $notFoundConfigurationEntries = [];

    /**
     * @inheritdoc
     * */
    public function __get($name)
    {
        $value = 'value';
        
        foreach(Yii::$app->params['app.supported_languages'] as $language)
        {
            if(substr($name, strlen($name) - strlen($language) - 1) == '_'.$language)
            {
                $value .= '_'.$language;
                $name = substr($name, 0, strlen($name) - strlen($language) - 1);
                break;
            }
        }
        
        if(in_array($name, array_keys($this->configEntries)))
        {
            return $this->configEntries[$name]->$value;
        }
        
        try
        {
            return parent::__get($name);
        }
        catch (\yii\base\UnknownPropertyException $e)
        {
            // This might a configuration entry that does not yet exist in the database.
        }
        
        return null;
    }
    
    /**
     * @inheritdoc
     * */
    public function __set($name, $value)
    {
        $valueAttribute = 'value';
         
        foreach(Yii::$app->params['app.supported_languages'] as $language)
        {
            if(substr($name, strlen($name) - strlen($language) - 1) == '_'.$language)
            {
                $valueAttribute .= '_'.$language;
                $name = substr($name, 0, strlen($name) - strlen($language) - 1);
                break;
            }
        }
        
        if(in_array($name, array_keys($this->configEntries)))
        {
            $this->configEntries[$name]->$valueAttribute = $value;
            
            return;
        }
        
        try
        {
            parent::__set($name, $value);
        }
        catch (\yii\base\UnknownPropertyException $e)
        {
            // This configuration entry does not exist in the database.
            
            if(!$this->isAttributeSafe($name))
            {
                throw $e;
            }
            
            $this->configEntries[$name] = new ConfigEntry(['name' => $name, 'value' => $value]);
        }
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules[] = $this->_createRule(['app.incompatible_browsers', 'app.google_analytics_key', 'app.bing_app_id', 'app.facebook_admin_id'], ['safe']);
        $rules[] = $this->_createRule(['app.name', 'app.available_languages', 'app.main_domain', 'app.system_email'], ['required']);
        $rules[] = $this->_createRule(['app.available_languages'], ['validateAvailableLanguages']);
        $rules[] = $this->_createRule(['app.name', 'app.catchphrase', 'app.brand_name', 'app.owner_last_name', 'app.owner_name', 'yiingine.SocialMetas.meta_copyright'], ['string', 'max' => 255, 'min' => 2]);
        $rules[] = $this->_createRule(['app.main_domain', 'app.owner_street', 'app.owner_email1', 'app.owner_email2', 'app.system_email'], ['string', 'max' => 255, 'min' => 3]);
        $rules[] = $this->_createRule(['app.owner_email1', 'app.owner_email2', 'app.system_email'], ['email']);
        $rules[] = $this->_createRule(['app.owner_city', 'app.owner_postal_code', 'app.owner_country', 'app.owner_telephone1', 'app.owner_telephone2', 'app.owner_fax'], ['string', 'max' => 31, 'min' => 2]);
        $rules[] = $this->_createRule(['yiingine.SocialMetas.meta_keywords', 'yiingine.SocialMetas.meta_description'], ['string', 'min' => 2]);
        $rules[] = $this->_createRule(['yiingine.SocialMetas.default_thumbnail', 'app.default_background', 'app.main_logo', 'app.main_logo_reduced', 'app.favicon', 'app.apple_touch_icon', 'app.brand_logo'], ['\yiingine\validators\FileListValidator',
            'directory' => Yii::getAlias('@webroot/user/assets'),
            'extensions' => ['jpg', 'png', 'svg', 'gif'],
            'maxNumberOfFiles' => 1,
        ]);
        $rules[] = $this->_createRule(['app.alternate_domains',  'app.announcement'], ['string', 'max' => 1023, 'min' => 3]);
        $rules[] = $this->_createRule(['app.main_domain'], ['match', 'pattern' => '/^[a-zA-Z0-9_\.\-]+$/u']);
        $rules[] = $this->_createRule(['app.alternate_domains'], ['match', 'pattern' => '/^[a-zA-Z0-9_\.\,\ \-]+$/u']);
        $rules[] = $this->_createRule(['app.session_timeout'], ['integer', 'integerOnly' => true, 'min' => 120]);
        $rules[] = $this->_createRule(['app.require_javascript', 'ajaxNavigation.enabled', 'app.read_only', 'yiingine.users.disable_user_registration', 'yiingine.users.disable_user_accounts', 'app.emergency_maintenance_mode.enabled'], ['boolean']);
        
        return $rules;
    }
    
    /**
     * Creates a rule taking into account the transltable attributes.
     * @param array $attributes
     * @param array $rule
     * @return array the rule.
     * */
    private function _createRule($attributes, $rule)
    {
        $newAttributes = $attributes;
            
        foreach($attributes as $attribute)
        {
            if(!$this->isTranslatable($attribute))
            {
                continue;
            }
            
            foreach(Yii::$app->params['app.supported_languages'] as $language)
            {
                if($language != Yii::$app->getBaseLanguage())
                {
                    $newAttributes[] = $attribute.'_'.$language;
                }
            }
        }
        
        array_unshift($rule, $newAttributes);
        
        return $rule;
    }
    
    /** 
     * @inheritdoc
     * */
    public function init()
    {
        // Override of parent implementation to retrieve config entries.
        
        parent::init();
        
        $this->configEntries = [];
        
        foreach(ConfigEntry::find()->all() as $model)
        {
            $this->configEntries[$model->name] = $model;
        }
        
        // Do special processing on some configuration entries.
        $this->configEntries['app.available_languages']->value = explode(',', str_replace(' ', '', $this->configEntries['app.available_languages']->value));
    }
    
    /**
     * Validate that the available languages are within the list of supported languages.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateAvailableLanguages($attribute, $params)
    {        
        // The available languages have to be within the supported languages.
        if(array_diff($this->configEntries['app.available_languages']->value, Yii::$app->params['app.supported_languages']))
        {
            $this->addError($attribute, 'Unsupported language');
        }
    }
    
    
    /**
     * Check if a configuration entry is translatable.
     * @param string $name the name of the configuration entry.
     * @return boolean if the configuration entry is translatable.
     * */
    public function isTranslatable($name)
    {
        return isset($this->configEntries[$name]) && $this->configEntries[$name]->translatable;
    }
    
    /**
     * @param string $attribute the name of the attribute
     * @return array all the attributes that are translation of $attribute.
     * */
    public function getTranslationAttributes($attribute)
    {
        if(!$this->isTranslatable($attribute))
        {
            return [];
        }
        
        $attributes = [];
        
        foreach(Yii::$app->getParameter('app.supported_languages') as $language)
        {
            if($language == Yii::$app->getBaseLanguage())
            {
                $attributes[$language] = $attribute;
                continue;
            }
             
            $attributes[$language] = $attribute.'_'.$language;
        }
        
        return $attributes;
    }
    
    /** Save the contents of the form.
     * @param boolean $validate if validation should be run before saving. 
     * @return boolean if saving was successful.
     * */
    public function save($validate = true)
    {
        if($validate && !$this->validate())
        {
            return false;
        }
        
        if($validate)
        {
            $passedValidation = true;
            
            // Validate each configuration entry.
            foreach($this->configEntries as $name => $model)
            {
                if(!$model->validate())
                {
                    $this->addError($name, array_pop($model->getFirstErrors()));
                }
            }
            
            if(!$passedValidation)
            {
                return false;
            }
        }
        
        // Default thumbnail file management
        $fileUploader = new FileListUploader();
        $fileUploader->attribute = 'yiingine.SocialMetas.default_thumbnail';
        $fileUploader->model = $this;
        $fileUploader->directory = Yii::getAlias('@webroot/user/assets');
        $fileUploader->allowedExtensions = ['jpg', 'png'];
        $fileUploader->maxNumberOfFiles = 1;
        $fileUploader->init();
        $fileUploader->save();
        if(!$this->__get('yiingine.SocialMetas.default_thumbnail')) // If the file was deleted.
        {
            $fileUploader->purge(); // In case a file was deleted.
        }
        
        // Main logo file management
        $fileUploader = new FileListUploader();
        $fileUploader->attribute = 'app.main_logo';
        $fileUploader->model = $this;
        $fileUploader->directory = Yii::getAlias('@webroot/user/assets');
        $fileUploader->allowedExtensions = ['jpg', 'png', 'svg', 'gif'];
        $fileUploader->maxNumberOfFiles = 1;
        $fileUploader->init();
        $fileUploader->save();
        if(!$this->__get('app.main_logo')) // If the file was deleted.
        {
            $fileUploader->purge(); // In case a file was deleted.
        }
        
        // Main logo reduced file management
        $fileUploader = new FileListUploader();
        $fileUploader->attribute = 'app.main_logo_reduced';
        $fileUploader->model = $this;
        $fileUploader->directory = Yii::getAlias('@webroot/user/assets');
        $fileUploader->allowedExtensions = ['jpg', 'png', 'svg', 'gif'];
        $fileUploader->maxNumberOfFiles = 1;
        $fileUploader->init();
        $fileUploader->save();
        if(!$this->__get('app.main_logo_reduced')) // If the file was deleted.
        {
            $fileUploader->purge(); // In case a file was deleted.
        }
        
        // Favicon file management
        $fileUploader = new FileListUploader();
        $fileUploader->attribute = 'app.favicon';
        $fileUploader->model = $this;
        $fileUploader->directory = Yii::getAlias('@webroot/user/assets');
        $fileUploader->allowedExtensions = ['jpg', 'png', 'svg', 'gif'];
        $fileUploader->maxNumberOfFiles = 1;
        $fileUploader->init();
        $fileUploader->save();
        if(!$this->__get('app.favicon')) // If the file was deleted.
        {
            $fileUploader->purge(); // In case a file was deleted.
        }
        
        // Apple touch icon file management
        $fileUploader = new FileListUploader();
        $fileUploader->attribute = 'app.apple_touch_icon';
        $fileUploader->model = $this;
        $fileUploader->directory = Yii::getAlias('@webroot/user/assets');
        $fileUploader->allowedExtensions = ['png'];
        $fileUploader->maxNumberOfFiles = 1;
        $fileUploader->init();
        $fileUploader->save();
        if(!$this->__get('app.apple_touch_icon')) // If the file was deleted.
        {
            $fileUploader->purge(); // In case a file was deleted.
        }
        
        // Default background file management
        $fileUploader = new FileListUploader();
        $fileUploader->attribute = 'app.default_background';
        $fileUploader->model = $this;
        $fileUploader->directory = Yii::getAlias('@webroot/user/assets');
        $fileUploader->allowedExtensions = ['jpg', 'png', 'svg'];
        $fileUploader->maxNumberOfFiles = 1;
        $fileUploader->init();
        $fileUploader->save();
        if(!$this->__get('app.default_background')) // If the file was deleted.
        {
            $fileUploader->purge(); // In case a file was deleted.
        }
        
        // Do some special processing for certain attributes.
        $this->configEntries['app.available_languages']->value = implode(',', $this->configEntries['app.available_languages']->value);
        
        // Save entries that were modified.
        foreach($this->configEntries as $model)
        {
            if($model->getDirtyAttributes()) // If the entry was modified.
            {
                $model->save(false); // Validation was done earlier.
            }
        }
        
        $this->configEntries['app.available_languages']->value = explode(',', str_replace(' ', '', $this->configEntries['app.available_languages']->value));
        
        return true;
    }

    /** 
     * @inheritdoc
     * */
    public function attributeLabels()
    {
        return [
            'app.name' => Yii::t(__CLASS__, 'Application name'),
            'app.available_languages' => Yii::t(__CLASS__, 'Available languages'),
            'app.catchphrase' => Yii::t(__CLASS__, 'Catchphrase'),
            'app.main_domain' => Yii::t(__CLASS__, 'Main domain'),
            'app.alternate_domains' => Yii::t(__CLASS__, 'Alternate domains'),
            'yiingine.SocialMetas.meta_keywords' => Yii::t(__CLASS__, 'Meta keywords'),
            'yiingine.SocialMetas.meta_description' => Yii::t(__CLASS__, 'Meta description'),
            'app.session_timeout' => Yii::t(__CLASS__, 'Session timeout'),
            'yiingine.users.disable_user_registration' => Yii::t(__CLASS__, 'Disable user registration'),
            'yiingine.users.disable_user_accounts' => Yii::t(__CLASS__, 'Disable user accounts'),
            'app.system_email' => Yii::t(__CLASS__, 'Application e-mail'),
            'app.require_javascript' => Yii::t(__CLASS__, 'Require javascript'),
            'app.incompatible_browsers' => Yii::t(__CLASS__, 'Incompatible browsers'),
            'ajaxNavigation_dot_enabled' => Yii::t(__CLASS__, 'Enable AJAX navigation'),
            'app.main_logo' => Yii::t(__CLASS__, 'Logo'),
            'app.main_logo_reduced' => Yii::t(__CLASS__, 'Logo reduced'),
            'app.favicon' => Yii::t(__CLASS__, 'app.favicon'),
            'app.apple_touch_icon' => Yii::t(__CLASS__, 'Apple touch icon'),
            'app.owner_name' => Yii::t(__CLASS__, 'Owner name'),
            'app.owner_last_name' => Yii::t(__CLASS__, 'Owner last name'),
            'app.brand_name' => Yii::t(__CLASS__, 'Brand/company/corporate name'),
            'app.brand_logo' => Yii::t(__CLASS__, 'Brand/company/corporate logo'),
            'yiingine.SocialMetas.meta_copyright' => Yii::t(__CLASS__, 'Copyrights &copy;'),
            'app.owner_street' => Yii::t(__CLASS__, 'Street'),
            'app.owner_city' => Yii::t(__CLASS__, 'City'),
            'app.owner_postal_code' => Yii::t(__CLASS__, 'Postal code'),
            'app.owner_country' => Yii::t(__CLASS__, 'Country'),
            'app.owner_telephone1' => Yii::t(__CLASS__, 'Telephone'),
            'app.owner_telephone2' => Yii::t(__CLASS__, 'Telephone 2'),
            'app.owner_fax' => Yii::t(__CLASS__, 'Fax'),
            'app.owner_email1' => Yii::t(__CLASS__, 'E-mail'),
            'app.owner_email2' => Yii::t(__CLASS__, 'E-mail 2'),
            'yiingine.SocialMetas.default_thumbnail' => Yii::t(__CLASS__, 'Default thumbnail'),
            'app.default_background' => Yii::t(__CLASS__, 'Default background image'),
            'app.google_analytics_key' => Yii::t(__CLASS__, '<em>Google Analytics</em> key'),
            'app.bing_app_id' => Yii::t(__CLASS__, '<em>Bing AppID</em>'),
            'app.facebook_admin_id' => Yii::t(__CLASS__, '<em>Facebook Admin ID</em>'),
            'app.announcement' => Yii::t(__CLASS__, 'Annoucement'),
            'app.read_only' => Yii::t(__CLASS__, 'Read-only mode'),
            'app.emergency_maintenance_mode.enabled' => Yii::t(__CLASS__, 'Emergency maintenance mode')
        ];
    }
    
    /** 
     * @inheritdoc
     * */
    public function attributeDescriptions()
    {
        return [
            'app.name' => Yii::t(__CLASS__, 'The application name is used in the title for all pages of the web application.'),
            'app.catchphrase' => Yii::t(__CLASS__, 'The web application slogan. Displayed along with the name in the title of the front page.'),
            'app.available_languages' => Yii::t(__CLASS__, 'Languages available within the web application.'),
            'app.main_domain' => Yii::t(__CLASS__, 'The main domain used by the web application.'),
            'app.alternate_domains' => Yii::t(__CLASS__, 'The domains that should redirect to the main domain.'),
             'yiingine.SocialMetas.meta_keywords' => Yii::t(__CLASS__, 'Keywords separated by commas that best describe the site\'s content.'),
            'yiingine.SocialMetas.meta_description' => Yii::t(__CLASS__, 'A short description of the site\'s content.'),
            'app.session_timeout' => Yii::t(__CLASS__, 'The number of seconds without activity after which the user session expires.'),
            'yiingine.users.disable_user_registration' => Yii::t(__CLASS__, 'When checked access to the registration form is disabled.'),
            'yiingine.users.disable_user_accounts' => Yii::t(__CLASS__, 'Disable user accounts. Logged in users are automatically logged out. This should be accompanied by an announcement message.'),
            'app.require_javascript' => Yii::t(__CLASS__, 'When checked, if javascript is not enabled in the client browser, a warning message is displayed.'),
            'app.incompatible_browsers' => Yii::t(__CLASS__, 'If the client environement is in this list, <a href="{href}" target="_blank">a warning page</a> is displayed.', ['{href}' => \yii\helpers\Url::to(['site/updateClient'])]),
            'ajaxNavigation_dot_enabled' => Yii::t(__CLASS__, 'When checked, enable the AJAX navigation if this is available.'),
            'app.main_logo' => Yii::t(__CLASS__, 'The logo displayed in the navigation bar.'),
            'app.main_logo_reduced' => Yii::t(__CLASS__, 'The logo displayed in the navigation bar with small screen sizes.'),
            'app.favicon' => Yii::t(__CLASS__, 'Web browsers use favicons in the address bar, title bar, bookmarks, tabs and other shortcuts.'),
            'app.apple_touch_icon' => Yii::t(__CLASS__, 'Icon used to add the web application on Apple mobile home screen.'),
            'app.system_email' => Yii::t(__CLASS__, 'Default e-mail address used by the application.'),
            'yiingine.SocialMetas.default_thumbnail' => Yii::t(__CLASS__, 'Default thumbnail to display when the website is shared on social networks.'),
            'app.default_background' => Yii::t(__CLASS__, 'Background image used if no other has been specified.'),
            'app.google_analytics_key' => Yii::t(__CLASS__, 'The key provided by <em>Google Analytics</em> to synchronise the web application with their audience analysis services.'),
            'app.bing_app_id' => Yii::t(__CLASS__, 'The key provided by <em>Microsoft Bing</em> to synchronise the web application with their services.'),
            'app.facebook_admin_id' => Yii::t(__CLASS__, 'The key provided by <em>Facebook</em> to synchronise the web application with their services.'),
            'app.announcement' => Yii::t(__CLASS__, 'Text to display in a banner everywhere on the web application.'),
            'app.read_only' => Yii::t(__CLASS__, 'When checked, set the web application in read-only mode, which displays a warning message and prevents writes to the database.'),
            'app.emergency_maintenance_mode.enabled' => Yii::t(__CLASS__, 'Puts the site in emergency maintenance mode which prevents access to anyone excepts administrators.')
        ];
    }
    
    /** 
     * @inheritdoc
     * */
    public function getAttributeDescription($attribute)
    {
        $descriptions = $this->attributeDescriptions();
         
        return isset($descriptions[$attribute]) ? $descriptions[$attribute] : '';
    }
}
