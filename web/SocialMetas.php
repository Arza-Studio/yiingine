<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\web;

use \Yii;

/**
 * An application component for generating social tags.
 * see : http://developers.facebook.com/docs/share/
 * see : http://developers.facebook.com/tools/debug
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class SocialMetas extends \yii\base\Component
{            
    # CONTENT
    
    /** @var string html to parse for items to share. */
    public $content; 
    
    # BASIC TAGS
    
    /** @var mixed the title of the shared object. false if it should not be displayed. */
    public $title; 
    
    /** @var mixed the description of the shared object. false if it should not be displayed. */
    public $description; 
    
    /** @var mixed the keywords of the shared object. false if it should not be displayed. */
    public $keywords;
    
    /** @var mixed the absolute url of the thumbnail image illustrating the shared object. false if it should not be displayed. */
    // The thumbnail's width AND height must be at least 50 pixels, and cannot exceed 130x110 pixels.
    // The ratio of both height divided by width and width divided by height (w/h, h/w) cannot exceed 3.0. For example, an image of 126x39 pixels will not be displayed, as the ratio of width divided by height is greater than 3.0 (126/39 = 3.23). Images will be resized proportionally.
    public $thumbnail; 
    
    /** @var mixed the medium type the shared object.  false if it should not be displayed. */
    // Valid values at https://developers.facebook.com/docs/reference/opengraph/
    public $type; 
    
    /** @var mixed alternate url for the shared object. false if it should not be displayed.
     * NOTE: instead of scraping the current page for content, the service will scrape url. */
    public $url; 
    
    /** @var mixed the name of the site. false if it should not be displayed. */
    public $siteName;
    
    # AUDIO : The values title, description, image and audio are required to share an audio object
    
    /** @var mixed the absolute url of the audio object. false if it should not be displayed. */
    public $audioSource = false;
    
    /** @var mixed the type (format) of the audio object. false if it should not be displayed. */
    public $audioType = false;
    
    /** @var mixed the title of the audio object. false if it should not be displayed. */
    public $audioTitle = false;
    
    /** @var mixed the artist of the audio object. false if it should not be displayed. */
    public $audioArtist = false; 
    
    /** @var mixed the album of the audio object. false if it should not be displayed. */
    public $audioAlbum = false; 
    
    # VIDEO : The values title, description, image and audio are required to share an audio object
    
    /** @var mixed the absolute url of the video object. false if it should not be displayed. */
    public $videoSource = false;
    
    /** @var mixed the type (format) of the video object. false if it should not be displayed. */
    public $videoHeight = false;
    
    /** @var mixed the title of the video object. false if it should not be displayed. */
    public $videoWidth = false;
    
    /** @var mixed the type of the video object. false if it should not be displayed. */
    public $videoType = false; // example : application/x-shockwave-flash
    
    /**
     * Register all social metas.
     */
    public function register()
    {
        $view = Yii::$app->view;
        $controller = Yii::$app->controller;
        
        # TITLE
        
        if($this->title === null) // If no title has been defined.
        {
            $this->title = $view->title; // Use the page's title.
        }
        
        if($this->title !== false) // If the title tag should be displayed.
        {
            $view->registerMetaTag([
                'name' => 'title',
                'content' => $this->title,
                'property'=> 'og:title'
            ]);
        }
        
        # DESCRIPTION
        
        if($this->description === null) // If no description has been defined.
        {
            //If a meta description for the current module is set.
            if($controller->module && ($description = Yii::$app->getParameter($controller->module->id.'.meta_description')))
            {
                $this->description = $description; //Override the normal description.
            }
            // Else the description defined in the database will be used.
            else
            {
                $this->description = Yii::$app->getParameter('yiingine.SocialMetas.meta_description', false);
            }
        }        
        
        if($this->description !== false) // If the desciption should be displayed.
        {
            $view->registerMetaTag([
                'name' => 'description',
                'content' => $this->description,
                'property'=> 'og:description'
            ]);
        }
        
        # KEYWORDS
        
        if($this->keywords === null) // If no keywords have been defined.
        {
            //If a meta keywords for the current module is set.
            if($controller->module && ($keywords = Yii::$app->getParameter($controller->module->id.'.meta_keywords')))
            {
                $this->keywords = $keywords; //Override the normal keywords.
            }
            // Else the keywords defined in the database will be used.
            else
            {
                $this->keywords = Yii::$app->getParameter('yiingine.SocialMetas.meta_keywords', false);
            }
        }
        
        if($this->keywords !== false) // If the keywords should be displayed.
        {
            // Only display keywords if a super user is logged in.
            if(!Yii::$app->user->isGuest && Yii::$app->user->getIdentity()->superuser)
            {
                $view->registerMetaTag([
                    'name' => 'keywords',
                    'content' => $this->keywords,
                    'lang'=> Yii::$app->language
                ]);
            }
        }
        
        # THUMBNAIL
        
        if($this->thumbnail === null) // If no thumbnail has been defined.
        {
            if(Yii::$app->getParameter('yiingine.SocialMetas.meta_thumbnail')) // If a default thumbnail is defined. 
            {
                $this->thumbnail = '/user/assets/'.Yii::$app->getParameter('yiingine.SocialMetas.meta_thumbnail');    
            }
        }
        
        if($this->thumbnail !== false) // If the thumbnail should be displayed.
        {
            $view->registerLinkTag(['rel' => 'image_src', 'href' => $this->thumbnail]);
            $view->registerMetaTag([
                'name' => 'thumbnail_image',
                'content' => \yii\helpers\Url::to($this->thumbnail, true),
                'property'=> 'og:image'
            ]);
        }
        
        # TYPE
        
        if($this->type === null) // If the type has not been defined.
        {
            $this->type = 'website'; // Default generic type.
        }
        
        if($this->type !== false) // If the type should be displayed.
        {
            $view->registerMetaTag([
                'name' => 'medium_type',
                'content' => $this->type,
                'property'=> 'og:type'
            ]);
        }
        
        # URL
        
        if($this->url === null) // If the canonical url has not been defined.
        {
            $this->url = Yii::$app->urlManager->getCanonicalUrl();
        }
        
        if($this->url !== false) // If the url should be displayed.
        {
            $view->registerMetaTag([
                'name' => '',
                'content' => $this->url,
                'property'=> 'og:url'
            ]);
        }
        
        # SITE NAME
        
        if($this->siteName === null) // If no site name has been defined.
        {
            $this->siteName = Yii::$app->getParameter('app.name', false);
        }
        
        if($this->siteName !== false) // If the site_name should be displayed.
        {
            $view->registerMetaTag([
                'name' => 'site_name',
                'content' => $this->siteName,
                'property'=> 'og:site_name'
            ]);
        }
        
        # AUDIO SOURCE
        
        // Audio Source
        if($this->audioSource !== false) // If the audio_src tag should be displayed.
        {
            $view->registerLinkTag(['rel' => 'audio_src', 'href' => $this->audioSource]);
            $view->registerMetaTag([
                'name' => '',
                'content' => $this->audioSource,
                'property'=> 'og:audio'
            ]);
        }
        
        # AUDIO TYPE
        
        if($this->audioType !== false) // If the audio_type tag should be displayed.
        {
            $view->registerMetaTag([
                'name' => 'audio_type',
                'content' => $this->audioType,
                'property'=> 'og:audio:type'
            ]);
        }
        
        # AUDIO TITLE
        
        if($this->audioTitle !== false) // If the audio_title tag should be displayed.
        { 
            $view->registerMetaTag([
                'name' => 'audio_title',
                'content' => $this->audiotitle,
                'property'=> 'og:audio:title'
            ]);
        }
        
        # AUDIO ARTIST
        
        if($this->audioArtist !== false) // If the audio_artist tag should be displayed.
        {
            $view->registerMetaTag([
                'name' => 'audio_artist',
                'content' => $this->audioArtist,
                'property'=> 'og:audio:artist'
            ]);
        }
        
        # AUDIO ALBUM
        
        if($this->audioAlbum !== false) // If the audio_album tag should be displayed.
        {
            $view->registerMetaTag([
                'name' => 'audio_album',
                'content' => $this->audioAlbum,
                'property'=> 'og:audio:album'
            ]);
        }
        
        # VIDEO SOURCE
        
        if($this->videoSource !== false) // If the video_src tag should be displayed.
        {
            $view->registerLinkTag(['rel' => 'video_src', 'href' => $this->videoSource]);
            $view->registerMetaTag([
                'name' => '',
                'content' => $this->videoSource,
                'property'=> 'og:video'
            ]);
        }
        
        # VIDEO WIDTH
        
        if($this->videoWidth !== false) // If the video_width tag should be displayed.
        {
            $view->registerMetaTag([
                'name' => 'video_width',
                'content' => $this->videoWidth,
                'property'=> 'og:video:width'
            ]);
        }
        
        # VIDEO HEIGHT
        
        if($this->videoHeight !== false) // If the video_height tag should be displayed.
        {
            $view->registerMetaTag([
                'name' => 'video_height',
                'content' => $this->videoHeight,
                'property'=> 'og:video:height'
            ]);
        }
        
        # VIDEO TYPE
        
        if($this->videoType !== false) // If the video_type tag should be displayed.
        {
            $view->registerMetaTag([
                'name' => 'video_type',
                'content' => $this->videoType,
                'property'=> 'og:video:type'
            ]);
        }
        
        # META ROBOTS
        
        // Robots are blocked from the site when in debug mode or in the admin.
        if(Yii::$app->controller->getSide() == \yiingine\web\Controller::ADMIN || YII_DEBUG)
        {            
            Yii::$app->params['yiingine.SocialMetas.meta_robots'] = 'NOINDEX, NOFOLLOW';
        }

        # GENERAL META TAGS
        
        // Iterate through all params because this is where meta tags are defined.
        foreach(Yii::$app->params as $key => $value)
        {   
            // If this is not a meta tag or it has no value.
            if(strpos($key, 'yiingine.SocialMetas.meta_') !== 0 || !$value)
            { 
                continue; // Skip it.
            }
            
            $key = substr($key, 26); // Remove the "yiingine.SocialMetas.meta_" part from the entry to get the tag name.
            
            switch($key) // Special processing for certain tags.
            {
                case 'copyright':
                    $value = $value.' '.date('Y'); // Add the year automatically.
                    break;
                case 'keywords':
                case 'description':
                case 'thumbnail':
                    continue; // Those tags have been processed earlier.
            }
            
            $view->registerMetaTag(['name' => $key, 'content' => $value]);
        } 
    } 
}
