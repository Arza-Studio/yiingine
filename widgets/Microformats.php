<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \Yii;
use \yii\helpers\Html;
use \yii\helpers\Url;

/**
 * Address is a widget that create a proper hcard
 * see : http://microformats.org/wiki/hcard-authoring
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class Microformats extends \yii\base\Widget
{
    /* View Options */
    
    /** @var boolean set to true to use HiddenText widget on the phone numbers. */
    public $hiddenPhoneNumbers = true;
    
    /** @var boolean set to true to use HiddenText widget on the emails. */
    public $hiddenEmails = true;
    
    /** @var boolean set to true to use tel: link on the phone numbers. */
    public $linkPhoneNumbers = true;
    
    /** @var boolean set to true to use mailto: link on the emails. */
    public $linkEmails = true;
    
    /** @var boolean set to true to use link on the urls. */
    public $linkUrls = true;
    
    /** @var mixed
     *  Used if we need to display the hidden text for a particular hostname or IP address.
     *  Those exceptions are listed in an array.
     *  If the exceptions value is false (default), no exceptions are allowed.
     */
    public $hiddenTextExceptions = ['h2vx.com']; // H2VX is contacts conversion service.
    
    /* vCard Fields */
    
    /** @var string the organisation name. */
    public $organization = '';
    
    /** @var string the first name. */
    public $firstName = '';
    
    /** @var string the additional name (middle name or middle initial). */
    public $additionalName = '';
    
    /** @var string the last name. */
    public $lastName = '';
    
    /** @var array the adresses list. 
    $addresses = [
        [
            'streetAddress' => 'value',
            'postalCode' => 'value',
            'locality' => 'value', // (city)
            'countryName' => 'value'
        ],
        [
            ...
    ]
    */
    public $addresses = [];
    
    /** @var array the phone numbers list. 
    $phoneNumbers = [
        [
            'type' => 'work',
            'value' => '+0000000000',
            'label' => 'Office: ',
            'hiddenMessage => 'Show office phone number'
        ],
        [
            ...
    ]
    type : VOICE, home, msg, work, pref, fax, cell, video, pager, bbs, modem, car, isdn, pcs 
    */
    public $phoneNumbers = [];
    
    /** @var array the emails list.
    $emails = [
        [
            'value' => 'email@sample.com',
            'label' => 'Sample email: ',
            'hiddenMessage => 'Show sample email'
        ],
        [
            ...
    ]
    */
    public $emails = [];
    
    /** @var array the emails list.
    $urls = [
        [
            'value' => 'sample.com',
            'label' => 'Sample url: ',
        ],
        [
            ...
    ]
    */
    public $urls = [];
    
    /** @return array the list of actions used by this widget.*/
    public static function actions()
    {
        return [
            'address.getVCard' => [
                'class' => '\yiingine\widgets\GetVCardAction',
            ]
        ];    
    }
    
    /**
    * @inheritdoc
    */
    public function run()
    {
        AddressAsset::register($this->view);
        
        # Identity
        $return = Html::beginTag('li').Html::beginTag('ul');
        // Given name
        $firstName = ($this->firstName != '') ? Html::tag('span', $this->firstName, ['class' => 'given-name']) : '' ;
        // Additional name
        $additionalName = ($this->additionalName != '') ? (($this->firstName != '') ? ' ' : '').Html::tag('abbr', $this->additionalName, ['class' => 'additional-name']) : '' ;
        // Family name
        $lastName = ($this->lastName != '') ? (($this->firstName.$this->additionalName != '') ? ' ' : '').Html::tag('span', $this->lastName, ['class' => 'family-name']) : '' ;
        // Complete Name
        $return .= $completeName = ($firstName.$additionalName.$lastName != '') ? Html::tag('li', $firstName.$additionalName.$lastName, ['class' => 'n']) : '' ;
        // Organization
        $return .= $organization = ($this->organization != '') ? Html::tag('li', $this->organization, ['class' => 'fn org']) : '' ;
        $return .= Html::endTag('ul').Html::endTag('li');
        
        # Addresses
        if(!empty($this->addresses))
        {
            $addresses = Html::beginTag('li');
            foreach($this->addresses as $address)
            {
                // Street address
                $streetAddress = (isset($address['streetAddress']) && $address['streetAddress'] != '') ? Html::tag('li', $address['streetAddress'], ['class' => 'street-address']) : '' ;
                // Postal code
                $postalCode = (isset($address['postalCode']) && $address['postalCode'] != '') ? Html::tag('span', $address['postalCode'], ['class' => 'postal-code']) : '' ;
                // Locality
                $locality = (isset($address['locality']) && $address['locality'] != '') ? (($postalCode != '') ? ' ' : '').Html::tag('span', $address['locality'], ['class' => 'locality']) : '' ;
                // Postal code + Locality
                $postalCodeAndLocality = ($postalCode.$locality != '') ? Html::tag('li', $postalCode.$locality) : '' ;
                // Country Name
                $countryName = (isset($address['countryName']) && $address['countryName'] != '') ? Html::tag('li', $address['countryName'], ['class' => 'country-name']) : '' ;
                // Complete address
                $return .= $addresses .= ($streetAddress.$postalCodeAndLocality.$countryName != '') ? Html::tag('ul', $streetAddress.$postalCodeAndLocality.$countryName, ['class' => 'adr']) : '' ;
            }
            $addresses = Html::endTag('li');
        }
        
        # Phone Numbers
        if(!empty($this->phoneNumbers))
        {
            $phoneNumbers = Html::beginTag('li').Html::beginTag('ul');
            foreach($this->phoneNumbers as $i => $phoneNumber)
            {
                $label = (isset($phoneNumber['label']) && $phoneNumber['label'] != '') ? $phoneNumber['label'] : '';
                $type = (isset($phoneNumber['type']) && $phoneNumber['type'] != '') ? Html::tag('span', $phoneNumber['type'], ['class' => 'type']) : '';
                $value = (isset($phoneNumber['value']) && $phoneNumber['value'] != '') ? Html::tag('span', $phoneNumber['value'], ['class' => 'value']) : '';
                if($value != '')
                {
                    if($this->linkPhoneNumbers)
                    {
                        $html = $label.Html::a($type.$value, 'tel:'.$phoneNumber['value'], ['class' => 'tel']);
                    }
                    else
                    {
                        $html = $label.Html::tag('span', $type.$value, ['class' => 'tel']);
                    }
                    if($this->hiddenPhoneNumbers)
                    {
                        if(isset($phoneNumber['hiddenMessage']))
                        {
                            $message = $phoneNumber['hiddenMessage'];
                        }
                        elseif(count($this->phoneNumbers) > 1)
                        {
                            $message = Yii::tA(['en' => 'Display phone number {n}', 'fr' => 'Afficher le numéro de téléphone {n}'], ['n' => $i + 1]);
                        }
                        else
                        {
                            $message = Yii::tA(['en' => 'Display phone number', 'fr' => 'Afficher le numéro de téléphone']);
                        }
                        
                        $phoneNumbers .= Html::tag('li',
                            \yiingine\widgets\HiddenText::widget([
                                'message' => $message,
                                'text' =>  $html,
                                'exceptions' => $this->hiddenTextExceptions
                            ])
                        );
                    }
                    else
                    {
                        $phoneNumbers .= Html::tag('li', $html);
                    }
                }
                $i++;
            }
            $return .= $phoneNumbers .= Html::endTag('ul').Html::endTag('li');
        }
        
        # Emails
        if(!empty($this->emails))
        {
            $emails = Html::beginTag('li').Html::beginTag('ul');
            foreach($this->emails as $i => $email)
            {
                $label = (isset($email['label']) && $email['label'] != '') ? $email['label'] : '';
                $value = (isset($email['value']) && $email['value'] != '') ? Html::tag('span', $email['value'], ['class' => 'email']) : '';
                if($value != '')
                {
                    if($this->linkEmails)
                    {
                        $html = $label.Html::a($value, 'mailto:'.$email['value']);
                    }
                    else
                    {
                        $html = $label.$value;
                    }
                    if($this->hiddenEmails)
                    {
                        if(isset($email['hiddenMessage']))
                        {
                            $message = $email['hiddenMessage'];
                        }
                        elseif(count($this->phoneNumbers) > 1)
                        {
                            $message = Yii::tA(['en' => 'Display email {n}', 'fr' => 'Afficher l\'email {n}'], ['n' => $i + 1]);
                        }
                        else
                        {
                            $message = Yii::tA(['en' => 'Display email', 'fr' => 'Afficher l\'email']);
                        }
                        $emails .= Html::tag('li',
                            \yiingine\widgets\HiddenText::widget([
                                'message' => $message,
                                'text' =>  $html,
                                'exceptions' => $this->hiddenTextExceptions
                            ])
                        );
                    }
                    else
                    {
                        $emails .= Html::tag('li', $html);
                    }
                }
            }
            $return .= $emails .= Html::endTag('ul').Html::endTag('li');
        }
        
        # Urls
        if(!empty($this->urls))
        {
            $urls = Html::beginTag('li').Html::beginTag('ul');
            foreach($this->urls as $i => $url)
            {
                $label = (isset($url['label']) && $url['label'] != '') ? $url['label'] : '';
                $value = (isset($url['value']) && $url['value'] != '') ? Html::tag('span', $url['value'], ['class' => 'url']) : '';
                if($value != '')
                {
                    if($this->linkUrls)
                    {
                        $href = preg_match("~^(?:f|ht)tps?://~i", $url['value']) ? $url['value'] : 'http://'.$url['value'] ;
                        $html = $label.Html::a($value, $href, ['target'=>'_blank']);
                    }
                    else
                    {
                        $html = $label.$value;
                    }
                    $urls .= Html::tag('li', $html);
                }
            }
            $return .= $urls .= Html::endTag('ul').Html::endTag('li');
        }
        
        return Html::tag('ul', $return, ['class' => 'vcard']);
    }
}

/**
 * An action that returns a vCard.
 * */
class GetVCardAction extends \yii\base\Action
{
    /** 
     * @inheritdoc
     * */
    public function run($lastName = '', $firstName = '', $phone = '', $street = '', $city = '', $postalCode = '', $country = '', $email = '', $organisation = '', $title = '', $url = '', $photo = '')
    {                
        include Yii::getAlias('@yiingine/vendor/vCard/vCard.php');
        
        $v = new \vCard();

        $v->setName($lastName, $firstName, '', '');
        $v->setPhoneNumber($phone, 'PREF;HOME;VOICE');
        //$v->setBirthday("1960-07-31");
        $v->setAddress('', '', $street, $city, '', $postalCode, $country);
        $v->setEmail($email);
        $v->setOrg($organisation);
        $v->setTitle($title);
        $v->setURL($url, 'WORK');
        if($photo != '')
        {
            $v->setPhoto(file_get_contents(substr(urldecode($photo),1)), 'JPEG');
        }
        //$v->setNote("You can take some notes here.\r\nMultiple lines are supported via \\r\\n.");

        Yii::$app->response->sendContentAsFile($v->getVCard(), $v->getFileName(), ['mimeType' => 'text/x-vCard']);
    }
}

/**
 * The asset bundle for the Address widget.
 * */
class AddressAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/widgets/assets/';
    
    /** @inheritdoc */
    public $css = ['address/address.css'];
}
