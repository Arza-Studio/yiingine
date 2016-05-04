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
 * Clean and optimize image tags.
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class OptimizeImgs extends \yii\base\Widget 
{
    
    /** @var string the html code to treat. */
    public $html = null;
    
    /** @var boolean set true to remove all the code around the img tags. */
    public $returnImgsOnly = false;
    
    /** @var boolean set true to remove the paragraph tag around the img tags. */
    public $unwrapParagraph = false;
    
    /** @var boolean set true to remove the width attribute in the image. */
    public $removeWidth = true;
    
    /** @var boolean set true to remove the height attribute in the image. */
    public $removeHeight = true;
    
    /** @var boolean set true to add an overlay to zoom on the image. */
    public $addZoomOverlay = false;
    
    /** @var array the html attributes to replace in img tags. */
    public $options = [];
    
    /** @var string used to wrap the img tags with html where {img} will be replaced by each img tag.  */
    public $layout = '{img}';
    
    /** 
     * Return the isolated img tags in an array. 
     */
    protected function getImgs()
    {
        // Imgs tags isolation (we are not using html parser here to be able replace exact string if required).
        if($this->unwrapParagraph && preg_match_all('#(<p><img[^>]+></p>)#i', $this->html, $result) >= 1)
        {
            return $result[0];
        }
        elseif(preg_match_all('#(<img[^>]+>)#i', $this->html, $result) >= 1)
        {
            return $result[0];
        }

        return false;
    }
    
    /**
     * Executes the widget.
     * This method is called by {@link CBaseController::endWidget}.
     */
    public function run() 
    { 
        // If only the iframes tags must be return : addition method.
        if($this->returnImgsOnly)
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
        
        // If img tags has been found.
        if($imgs = $this->getImgs())
        {
            foreach($imgs as $img)
            {
                // Get the image attributes via html parsing (see : http://simplehtmldom.sourceforge.net/).
                $options = SimpleHTMLDom::str_get_html($img)->find('img', 0)->getAllAttributes();
                
                // If the image should not be optimized.
                if(isset($options['class']) &&  strpos($options['class'], 'no-optimization') !== false)
                {
                    continue;
                }
                
                // Check if the img tag has not been optimized yet.
                if(isset($options['data-optimized']))
                {
                    continue; // Several optimization are not allowed.
                }
                else
                {
                    $options['data-optimized'] = 'true';
                }
                // If the removing of the width attribute is requested.
                if($this->removeWidth)
                {
                    unset($options['width']);
                }
                // If the removing of the height attribute is requested.
                if($this->removeHeight)
                {
                    unset($options['height']);
                }
                
                // If the html attributes must be remplaced.
                if(!empty($this->options))
                {
                    $options = array_merge_recursive($options, $this->options);
                }
                // Manage src attribute
                $src = isset($options['src']) ? $options['src'] : '';
                unset($options['src']);
                // Add overlay on image with zoom on click event
                if($this->addZoomOverlay && !empty($src))
                {
                    $options['data-zoomoverlay'] = uniqid();
                    // Overlay + MagnificPopup
                    \roman444uk\magnificPopup\MagnificPopupAsset::register($this->view);
                    \yiingine\widgets\Overlay::widget([
                        'selector' => '[data-zoomoverlay="'.$options['data-zoomoverlay'].'"]',
                        'content' => \rmrevin\yii\fontawesome\FA::icon('expand')->size(\rmrevin\yii\fontawesome\FA::SIZE_4X),
                        'onClick' => 'obj.overlay.magnificPopup({items: [{"src": "'.$src.'", "type": "image"}]});',
                    ]);
                }
                // Rebuild img tag and replace it in the layout.
                $optimizedImg = str_replace('{img}', Html::img($src, $options), $this->layout);
                // If only the iframes tags must be return : addition method.
                if($this->returnImgsOnly)
                {
                    // Add the optimized iframe string in return variable.
                    $return .= $optimizedImg;
                }
                // Otherwise : replacement method.
                else
                {
                    // Replace the original iframe with the optimized one.
                    $return = str_replace($img, $optimizedImg, $return);
                }
            }
        }
        
        return $return;
    }
}
