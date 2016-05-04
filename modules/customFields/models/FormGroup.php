<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\models;

use \yiingine\caching\GroupCacheDependency;
use \Yii;

/**
* This active record class is in charge of managing the 'form_group' table.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class FormGroup extends \yiingine\db\TranslatableActiveRecord implements \yiingine\db\AdministrableInterface
{
    /** @var CustomFieldsModule the module to which this form group is attached.*/
    public static $customFieldsModule = null;
    
    /** 
     * @inheritdoc
     * */
    public static function getModelLabel($plural = false)
    {
        return Yii::t(__CLASS__, '{n, plural, =1{Form group}other{Form groups}}', ['n' => $plural ? 2 : 1]);
    }
    
    /**
     * @return string the associated database table name
     */
    public static function tableName()
    { 
        return 'form_groups';
    }
    
    /** 
     * @inheritdoc
     * */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'ActiveRecordOrderingBehavior' => ['class' => '\yiingine\behaviors\ActiveRecordOrderingBehavior', 'groupingAttributes' => ['owner']]
        ]);
    }
    
    /**
     * @inheritdoc
     * */
    public function translatableAttributes()
    {
        return ['name'];
    }
    
    /** 
     * @inheritdoc
     * */
    public function rules()
    {
        /* NOTE: you should only define rules for those attributes that
         * will receive user inputs.*/
        return [
            [['name', 'collapsed', 'level'], 'required'],
            ['name', 'unique', 'filter' => ['owner' => $this->owner]],
            ['name', 'string', 'max' => 255, 'min' => 1],
            ['position', 'integer', 'integerOnly' => true, 'min' => 0],
            ['parent_id', 'exist', 'targetAttribute' => 'id', 'filter' => ['owner' => self::$customFieldsModule->tableName], 'targetClass' => get_class($this), 'when' => function($model){ return (int)$model->parent_id !== 0; }],
            ['level', 'integer', 'integerOnly' => true, 'min' => 1],
            ['level', 'default', 'value' => 1],
            ['collapsed', 'boolean'],
            ['collapsed', 'default', 'value' => 0],
            /* The following rule is used by search().
             * Please remove those attributes that should not be searched. */
            [['id', 'name', 'owner', 'collapsed', 'ts_updt'], 'safe', 'on' => 'search'],
        ];
    }
    
    /**
     * @return array all the CustomFields in this form group.
     * */
    public function getCustomFields()
    {
        return $this->hasMany(\yiingine\modules\customFields\models\CustomField::className(), ['form_group_id' => 'id'])->orderBy('position');
    }
    
    /**
     * @return FormGroup the parent of this formGroup or null if it does not exist.
     * */
    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_id']);
    }
    
    /**
     * @return array all this FormGroup's children.
     * */
    public function getFormGroups()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'id'])->orderBy('position')->inverseOf('parent');
    }
    
    /** 
     * @inheritdoc 
     * */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);        
        GroupCacheDependency::deleteGroup('CustomField');
    }
    
    /** 
     * @inheritdoc
     * */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // Set form_group_id to 0 on all the fields that used to refer to this form_group.
        CustomField::updateAllWithEvents(['form_group_id' => 0], ['form_group_id' => $this->id], [], true);
        
        GroupCacheDependency::deleteGroup('CustomField');
        
        // Reassign all children to this form group's parent.
        foreach($this->formGroups as $formGroup)
        {
            $formGroup->parent_id = $this->parent_id;
            $formGroup->save();
        }
    }
    
    /** 
     * @inheritdoc
     * */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name' => Yii::t(__CLASS__, 'Name'),
            'position' => Yii::t(__CLASS__, 'Position'),
            'level' => Yii::t(__CLASS__, 'Level'),
            'parent_id' => Yii::t(__CLASS__, 'Parent'),
            'owner' => Yii::t(__CLASS__, 'Associated table'),
            'collapsed' => Yii::t(__CLASS__, 'Collapsed'),
        ]);
    }
    
    /** 
     * @inheritdoc
     * */
    public function attributeDescriptions()
    {
        return [
            'name' => Yii::t(__CLASS__, 'The name of the form group.'),
            'level' => Yii::t(__CLASS__, 'The level at which the form group is to be displayed. This field is used by themes to position form elements.'),
            'parent_id' => Yii::t(__CLASS__, 'Parent form group'),
            'collapsed' => Yii::t(__CLASS__, 'If the form group should be collapsed by default.'),
        ];
    }
    
    /**
     * @inheritdoc
     */
    protected function searchInternal($dataProvider)
    {
        $dataProvider = parent::searchInternal($dataProvider);
        
        $dataProvider->query->andFilterWhere(['like', 'name', $this->name])->andFilterWhere(['collapsed' => $this->collapsed]);

        return $dataProvider;
    }
    
    /** 
     * @inheritdoc
     * */
    public function getAdminUrl()
    {
        if(!self::$customFieldsModule) // If not customfields module has been set.
        {
            return false; // Cannot return a URL.
        }
        
        if($this->isNewRecord)
        {
            return ['/'.self::$customFieldsModule->id.'/admin/formGroup/create'];
        }

        return ['/'.self::$customFieldsModule->id.'/admin/formGroup/update', 'id' => $this->id];
    }
    
    /** 
     * @inheritdoc
     * */
    public function isAccessible()
    {
        return Yii::$app->checkAccess('FormGroup-view');
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
        return true; // Form groups cannot be disabled.
    }
}
