<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

/**
 * An interface model classes must implement to be compabible with this module.
 * */
interface CustomizableModelInterface
{        
    /** @param boolean $hidden also get hidden fields. 
     * @return array an associative array of all the name => CustomField that belong
     *  to this class.*/
    public function getFields($hidden = true);
    
    /** Fetches a particular field.
     * @param string $name the name of the field.
     * @return CustomField the field, null if it does not exist.*/
    public function getField($name);
    
    /** @return array an associative array of all the name => CFieldManager for
     * this class' custom fields.*/
    public function getManagers();
    
    /** Fetches a particular manager.
     * @param string $name the name of the manger.
     * @return CFieldManager the manager or null if it does not exist.*/
    public function getManager($name);
    
    /** @return CustomFieldsModule the module for this model's custom fields.*/
    public function getCustomFieldsModule();
}
