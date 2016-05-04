<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
 
namespace yiingine\modules\media\models;

/**
 * A model for custom fields of type URLREWRITING.
 * */
class UrlRewritingField extends \yiingine\modules\customFields\models\CustomField
{            
    /** 
     * @inheritdoc
     * */
    public function rules()
    {
        return array_merge([
            //All attributes are deactivated for this field type because it is always placed in the Medium fieldset.
            [['min_size', 'size', 'configuration', 'default', 'form_group_id', 'translatable', 'required'], '\yiingine\validators\UnsafeValidator'], //These attributes should not be displayed or used.
            [['in_forms', 'required'], 'default', 'value' => 0],
            ['translatable', 'default', 'value' => 1],
        ], parent::rules());
    }
    
    /** 
     * @inheritdoc
     * */
    protected function createOrUpdateField() {}
    
    /** 
     * @inheritdoc
     * */
    protected function deleteFieldColumn() {}

    /** 
     * @inheritdoc
     * */
    public function getSql()
    {
        //This field has no SQL.
        return false;
    }
}
