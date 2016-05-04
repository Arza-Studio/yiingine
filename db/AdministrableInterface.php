<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
namespace yiingine\db;

/**
 * An interface for models that can be administered by the admin interface.
 */
interface AdministrableInterface
{
    /** @return string the url to the admin page for this model.*/
    public function getAdminUrl();
    
    /** @return boolean if the user has access to this model.*/
    public function isAccessible();
    
    /** @return string a string representation of the model. */
    public function __toString();
    
    /** Along whith this method, the model must define an "enabled()" scope. 
     * @return boolean whether this model is enabled or not.
     * */
    public function getEnabled();
}
