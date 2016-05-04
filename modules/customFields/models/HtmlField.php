<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

use \Yii;

/** A model for custom fields of type Html.*/
class HtmlField extends TextField
{
    /** @return array behaviors to attach to this model.*/
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'CustomFieldFilePathUpdateBehavior' => ['class' => '\yiingine\modules\customFields\behaviors\CustomFieldFilePathBehavior']
        ]);
    }
    
    /**
     * @return array validation rules for this model's attributes.
     */
    public function rules()
    {
        $rules = CustomField::rules();
        
        if(!$this->isNewRecord)
        {
            /* Do not allow changing the type of this field once it has been created because it handles
             * files on the filesystem. */
            $rules[] = ['type', '\yiingine\validators\UnsafeValidator'];
        }
        
        return $rules;
    }
    
    /**
     * Validate the configuration attribute.
     * @param string $attribute the attribute to validate.
     * @param array $params the parameters for the validator.
     * */
    public function validateConfiguration($attribute, $params) 
    {
        CustomField::validateConfiguration($attribute, $params);
    }
    
    /** Returns an example configuration for this field to be used inside the descriptions
     * and as a default value. Has to be valid php.
     * @return string an example configuration.*/
    public function getExampleConfiguration()
    {
        return 
'array(
    "height" => "450px",
    "options" => array(
        "theme_advanced_buttons1" => "formatselect,|,bold,italic,underline,strikethrough,|,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,indent,outdent",
        "theme_advanced_buttons2" => "tablecontrols,|,uploadImage,image,media,createGallery",
        "theme_advanced_buttons3" => "undo,redo,|,removeformat,cleanup,|,pastetext,pasteword,|,link,unlink,anchor,|,code,|,search,replace,|,fullscreen",
        "forced_root_block" => "p",
        "force_br_newlines" => false,
        "force_p_newlines" => true,
        "char_counter" => 1000,
        "theme_advanced_blockformats" => array("Title"=>"h1", "Subtitle 1"=>"h2", "Paragraph"=>"p"),
    )
);';
    }
    
    /**
     * @inheritdoc
     * */
    public function beforeDelete()
    {
        $class = CustomField::getModule()->modelClass;
    
        foreach($class::find()->where(['not', [$this->name => null, $this->name => '']])->all() as $model)
        {
            // Simulate a deletion of the model to get rid of the files.
            $model->getManager($this->name)->beforeDelete(new \yii\base\ModelEvent());
        }
            
        return parent::afterDelete();
    }
}
