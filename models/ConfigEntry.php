<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\models;

use \Yii;

/**
* This active record class is in charge of managing the 'config' table.
* This table holds configurations entries for the application.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class ConfigEntry extends \yiingine\db\TranslatableActiveRecord implements \yiingine\db\ModelInterface, \yiingine\db\AdministrableInterface
{
    /**
    * @inheritdoc
    */
    public static function getModelLabel($plural = false)
    {
        return Yii::t(__CLASS__, '{n, plural, =1{Configuration Entry}other{Configuration Entries}}', ['n' => $plural ? 2 : 1]);
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName() { return 'config'; }
    
    /**
     * @inheritdoc
     * */
    public function translatableAttributes()
    {
        return $this->getAttribute('translatable') || $this->getIsNewRecord()  ? ['value'] : [];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        /* NOTE: you should only define rules for those attributes that
         * will receive user inputs.*/
        return array_merge(parent::rules(), [
            ['name', 'required'],
            ['name', 'unique'],
            ['translatable', 'boolean'],
            ['translatable', 'default', 'value' => 0],
            ['name', 'string', 'max' => 128, 'min' => 1],
            ['value', 'safe']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name' => Yii::t(__CLASS__, 'Name'),
            'value' => Yii::t(__CLASS__, 'Value'),
            'translatable' => Yii::t(__CLASS__, 'Translatable'), 
        ]);
    }
    
    /**
     * @inheritdoc
     */
    public function attributeDescriptions()
    {
        return array_merge(parent::attributeDescriptions(), [
            'name' => Yii::t(__CLASS__, 'The name of the configuration entry.'),
            'value' => Yii::t(__CLASS__, 'The value of the configuration entry.'),
            'translatable' => Yii::t(__CLASS__, 'If the value should be translatable or not.'), 
        ]);
    }

    /** 
     * @inheritdoc 
     * */
    public function getAdminUrl()
    {
        return ['/admin/config/update', 'id' => $this->id];
    }
    
    /** 
     * @inheritdoc
     * */
    public function isAccessible()
    {
        return Yii::$app->user->can($this->formName().'-view'); // Use normal access checking.    
    }
    
    /** 
     * @inheritdoc
     * */
    public function __toString() 
    { 
        return $this->name; 
    }
    
    /** 
     * @inheritdoc
     * */
    public function getEnabled() 
    { 
        return true; // This model cannot be disabled.
    }
}
