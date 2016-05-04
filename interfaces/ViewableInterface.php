<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\interfaces;

/**
 * An interface for models that should be viewable by clients.
 */
interface ViewableInterface
{
    /** @param boolean if the title will be used within html. If not, it will be stripped
     * from its tags.
     * @return string a user-friendly title for the model.*/
    public function getTitle($html = false);
    
    /** @return string a user-friendly name for the model. A descriptor
     * is meant to identify a model while a title is meant to be integrated
     * as an heading for text. */
    public function getDescriptor();
    
    /** @return string a short description of the model's contents.*/
    public function getDescription();
    
    /** @return string a path to a thumbnail for the model.*/
    public function getThumbnail();
    
    /** @return string content excerpt for the model. This can be anything from an excerpt
     * to complete html code.*/
    public function getContent();
    
    /** @return mixed an url or array to be given to Url::to() to display the model within the site. false if the model
     * cannot be displayed.*/
    public function getUrl();
}
