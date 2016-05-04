<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \Yii;
use \yii\helpers\Html;
use \yii\web\View;
use \keltstr\simplehtmldom\SimpleHTMLDom;

/**
 * Clean and optimize iframe tags.
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class OptimizeIframes extends \yii\base\Widget 
{
    
    /** @var string the html code to treat. */
    public $html = null;
    
    /** @var boolean set true to remove all the code around the iframe tags. */
    public $returnIframesOnly = false;
    
    /** @var boolean set true to remove the width attribute in the iframes. */
    public $removeWidth = true;
    
    /** @var boolean set true to remove the height attribute in the iframes. */
    public $removeHeight = true;
    
    /** @var boolean set true to clean ads, controls has much as possible through the video player request. */
    public $cleanVideoRequest = true;
    
    /** @var array the html attributes to remplace in img tags. */
    public $options = [];
    
    /** @var string used to wrap the iframe tags with html where {img} will be replaced by each iframe tag.  */
    public $layout = '{iframe}';
    
    /** 
     * Return the isolated iframe tag in an array. 
     */
    protected function getIframes()
    {
        // Iframes tags isolation (we are not using html parser here to be able replace exact string if required).
        if(preg_match_all('#(?:<iframe[^>]*)(?:(?:/>)|(?:>.*?</iframe>))#i', $this->html, $result) >= 1)
        {
            return $result[0];
        }
        else
        {
            return false;
        }
    }
    
    /** 
     * Clean ads and controls has much as possible through the player request in a iframe tag. 
     */
    protected function cleanVideoRequest($url)
    {
        // Url parsing.
        $urlToParse = $url;
        // If the url no match "http://" or "https://" :
        if(!preg_match("~^(?:f|ht)tps?://~i", $urlToParse))
        {
            // Exception for youtube
            $urlToParse = str_replace('//','http://', $urlToParse);
        }
        $parsedUrl = parse_url($urlToParse);
        // Remove query part in url ("?" and everything after)
        if(isset($parsedUrl['query']))
        {   
            $url = str_replace('?'.$parsedUrl['query'], '', $url);
        }
        // Youtube opitmization
        if($parsedUrl['host'] == 'www.youtube.com')
        {
            $optimisedUrl = $url.'?&showinfo=0&modestbranding=1&autohide=1&theme=light&rel=0';
        }
        // Dailymotion opitmization
        if($parsedUrl['host'] == 'www.dailymotion.com')
        {
            $optimisedUrl = $url.'?&logo=0&info=0';
        }
        // Vimeo opitmization
        if($parsedUrl['host'] == 'player.vimeo.com')
        {
            $optimisedUrl = $url.'?title=0&byline=0&portrait=0';
        }
        return $optimisedUrl;
    }
    
    /**
     * Executes the widget.
     * This method is called by {@link CBaseController::endWidget}.
     */
    public function run() 
    { 
        // If only the iframes tags must be return : addition method.
        if($this->returnIframesOnly)
        {
            // Start with an empty return variable.
            $return = '';
        }
        // Otherwise : replacement method.
        else
        {
            // Fill the return variable with the provided html string.
            $return = $this->html;
        }
        
        // If iframes has been found.
        if($iframes = $this->getIframes())
        {
            foreach($iframes as $iframe)
            {
                // Get the iframe attributes via html parsing (see : http://simplehtmldom.sourceforge.net/).
                $options = SimpleHTMLDom::str_get_html($iframe)->find('iframe', 0)->getAllAttributes();
                // Check if the iframe tag has not been optimized yet.
                if(isset($options['data-optimized']))
                {
                    continue; // Several optimization are not allowed.
                }
                else
                {
                    $options['data-optimized'] = 'true';
                }
                // If the removing of the width attribute is requested.
                if($this->removeWidth && isset($options['width']))
                {
                    unset($options['width']);
                }
                // If the removing of the height attribute is requested.
                if($this->removeHeight && isset($options['height']))
                {
                    unset($options['height']);
                }
                // If the iframe request must be cleaned.
                if($this->cleanVideoRequest && isset($options['src']))
                {
                    $options['src'] = $this->cleanVideoRequest($options['src']);
                }
                // If the html attributes must be remplaced.
                if(!empty($this->options))
                {
                    $options = array_merge_recursive($options, $this->options);
                }
                // Rebuild iframe tag and replace it in the layout.
                $optimizedIframe = str_replace('{iframe}', Html::tag('iframe', '', $options), $this->layout);
                // If only the iframes tags must be return : addition method.
                if($this->returnIframesOnly)
                {
                    // Add the optimized iframe string in return variable.
                    $return .= $optimizedIframe;
                }
                // Otherwise : replacement method.
                else
                {
                    // Replace the original iframe with the optimized one.
                    $return = str_replace($iframe, $optimizedIframe, $return);
                }
            }
        }
        
        return $return;
    }
}
