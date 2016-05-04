<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \Yii;

/**
 * A widget for generating links to social networks accounts.
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class ShareBox extends \yii\base\Widget
{   
    /** @var switchType can be : */
    const BUTTONS = 0;
    const POPOVER = 1;
    
    /** @var integer the type of switch to display the links. */
    public $type = self::BUTTONS;
    
    /**  @var string the url of the resource to share.' */
    public $url;
    
    /**  @var string the title of the resource to share.' */
    public $title;
    
    /**  @var string the description of the resource to share.' */
    public $description;
    
    /** @var array default services data. */
    private $defaultServicesData = [
        'facebook' => [
            'name' => 'Facebook',
            'icon' => 'facebook',
            'options' => [
                'data-color' => '#3b5998'
            ]
        ],
        'linkedin' => [
            'name' => 'LinkedIn',
            'icon' => 'linkedin',
            'options' => [
                'data-color' => '#0077b5'
            ]
        ],
        'gplus' => [
            'name' => 'Google Plus',
            'icon' => 'google-plus',
            'options' => [
                'data-color' => '#d95434'
            ]
        ],
        'twitter' => [
            'name' => 'Twitter',
            'icon' => 'twitter',
            'options' => [
                'data-color' => '#00acee'
            ]
        ],        
        'email' =>  [
            'name' => 'Email',
            'icon' => 'envelope-o',
            'options' => [
                'data-color' => '#000000'
            ]
        ],
    ];
    
    /** @var array additional or replacement social networks data.
    $customServicesData = [
        'kindle' => [
            'name' => 'Kindle',
            'icon' => 'my-kindle-icon',
            'options' => [
                'class' => 'mySuperClass'
            ]
        ],
        'gplus' => [
            'icon' => 'google-plus-square'
        ],
    ]
    */
    public $customServicesData = [];

    public $services = [
        'facebook',
        'linkedin',
        'gplus',
        'twitter',
        //'email' // Not working propaly see : SocialLinks
    ];
    
    /** @var array the default html attributes for all links. */
    public $options = [
        'class' => 'btn',
    ];
    
    /** @var boolean set true to remove data-color attributes. */
    public $removeDataColor = false;
    
    /** @var string the view that renders the widget. */
    public $viewName = 'shareBox';
        
    /**
    * @inheritdoc
    */
    public function run()
    {
        if(!$this->url)
        {
            return; // The widget is not rendered.
        }
        
        $links = []; // Will contain the formatted service links to be rendered by the widget.
        
        // Merge default services data with additional or replacement service data.
        $servicesData = array_replace_recursive($this->defaultServicesData, $this->customServicesData);
      
        // Extract information from 
        foreach($this->services as $service)
        {
            if(!isset($servicesData[$service]))
            {
                continue; // This social network is not known, skip it.
            }
            $serviceData = $servicesData[$service];
            // Set link label
            $links[$service]['label'] = '<i class="fa fa-'.$serviceData['icon'].'"></i>';
            // Building options
            // Merge default options with those specific to the service.
            $options = array_merge($this->options, isset($serviceData['options']) ? $serviceData['options'] : []);
            $options['title'] = Yii::t(__CLASS__, 'Share on {name}', ['name' => $serviceData['name']]);
            if($this->removeDataColor)
            {
                unset($options['data-color']);
            }
            $links[$service]['options'] = $options;
        }
        
        return $this->render($this->viewName, [
            'type' => $this->type,
            'url' => $this->url,
            'title' => $this->title,
            'description' => $this->description,
            'links' => $links
        ]);
    }
}
