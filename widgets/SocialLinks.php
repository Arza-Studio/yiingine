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
class SocialLinks extends \yii\base\Widget
{   
    /** @var string the social network profile url list. Set to null if none.
    $urls = 'https://twitter.com/ArzaStudio,https://www.facebook.com/ArzaStudio,...'
    */
    public $urls = null;
    
    /** @var array default social network data. */
    private $defaultNetworks = [
        'dribbble.com' => [
            'name' => 'Dribbble',
            'icon' => 'dribbble',
            'options' => [
                'data-color' => '#df4c83'
            ]
        ],
        'delicious.com' => [
            'name' => 'Delicious',
            'icon' => 'delicious',
            'options' => [
                'data-color' => '#008ced'
            ]
        ],
        'deviantart.com' => [
            'name' => 'Deviantart',
            'icon' => 'deviantart',
            'options' => [
                'data-color' => '#00d13b'
            ]
        ],
        'facebook.com' => [
            'name' => 'Facebook',
            'icon' => 'facebook',
            'options' => [
                'data-color' => '#3b5998'
            ]
        ],
        'flickr.com' =>  [
            'name' => 'Flickr',
            'icon' => 'flickr',
            'options' => [
                'data-color' => '#0062dd'
            ]
        ],
        'github.com' => [
            'name' => 'GitHub',
            'icon' => 'git',
            'options' => [
                'data-color' => '#333333'
            ]
        ],
        'instagram.com' => [
            'name' => 'Instagram',
            'icon' => 'instagram',
            'options' => [
                'data-color' => '#5185a6', null
            ]
        ],
        'linkedin.com' => [
            'name' => 'LinkedIn',
            'icon' => 'linkedin',
            'options' => [
                'data-color' => '#0077b5'
            ]
        ],
        'pinterest.com' => [
            'name' => 'Pinterest',
            'icon' => 'pinterest',
            'options' => [
                'data-color' => '#bd081c'
            ]
        ],
        'plus.google.com'=> [
            'name' => 'Google Plus',
            'icon' => 'google-plus',
            'options' => [
                'data-color' => '#d95434',
                'rel' => 'publisher'
            ]
        ],
        'reddit.com' => [
            'name' => 'Reddit',
            'icon' => 'reddit',
            'options' => [
                'data-color' => '#000000'
            ]
        ],
        'soundcloud.com' =>  [
            'name' => 'Soundcloud',
            'icon' => 'soundcloud',
            'options' => [
                'data-color' => '#f8610e'
            ]
        ],
        'stumbleupon.com' =>  [
            'name' => 'Stumble Upon',
            'icon' => 'stumbleupon',
            'options' => [
                'data-color' => '#eb4924'
            ]
        ],
        'twitter.com' => [
            'name' => 'Twitter',
            'icon' => 'twitter',
            'options' => [
                'data-color' => '#00acee'
            ]
        ],        
        'wordpress.com' =>  [
            'name' => 'Wordpress',
            'icon' => 'wordpress',
            'options' => [
                'data-color' => '#00769c'
            ]
        ],
        'youtube.com' =>  [
            'name' => 'YouTube',
            'icon' => 'youtube',
            'options' => [
                'data-color' => '#e11b2b'
            ]
        ],
    ];
    
    /** @var array additional or replacement social networks data.
    $networks = [
        'sample.com' => [
            'name' => 'My Super Blog',
            'icon' => 'heart',
            'options' => [
                'class' => 'mySuperBlog'
            ]
        ],
        'youtube.com' => [
            'icon' => 'youtube-square',
            'options' => [
                'title' => 'Visit my YouTube channel !'
            ]
        ],
    ]
    */
    public $networks = [];
    
    /** @var array the default html attributes for all links.
    $options = [
        'class' => 'btn',
    ]
    */
    public $options = [];
    
    /** @var boolean set true to remove data-color attributes. */
    public $removeDataColor = false;
    
    /** @var string the view that renders the widget. */
    public $view = 'socialLinks';
        
    /**
    * @inheritdoc
    */
    public function run()
    {
        if(!$this->urls) // If no social links are provided.
        {
            return; // The widget is not rendered.
        }
        
        $links = []; // Will contain the formatted social network links to be rendered by the widget.
        
        // Merge default networks data with additional or replacement social network data.
        $networks = array_replace_recursive($this->defaultNetworks, $this->networks);
      
        // Extract information from 
        foreach(explode(',', $this->urls) as $url)
        {
            $parsedUrl = parse_url($url);
            
            $name = str_replace('www.', '', $parsedUrl['host']); // Get the name of the social network from its domain.
            
            if(!isset($networks[$name]))
            {
                continue; // This social network is not known, skip it.
            }
            
            $network = $networks[$name];
            
            // Build the social link entry using data from the known networks.
            $links[$name] = [
                'url' => $url,
                'name' => isset($network['name']) ? $network['name'] : 'No name!',
                // If no icon was provided use the default 'times' icon.
                'icon' => isset($network['icon']) ? $network['icon'] : 'times',
            ];
            
            // Merge default options with those specific to the social network.
            $options = array_merge($this->options, isset($network['options']) ? $network['options'] : []);
            
            if(!isset($options['title'])) // If no title was provided.
            {
                $options['title'] = Yii::t(__CLASS__, 'Show {name} profile', ['name' => $name]);
            }
            
            if($this->removeDataColor)
            {
                unset($options['data-color']);
            }
            
            $links[$name]['options'] = $options;
        }
        
        return $this->render($this->view, ['links' => $links]);
    }
}
