<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\db;

use \Yii;

/**
 * This is an abstract class that inherits from yiingine's ActiveRecord to automate the following functions:
 *
 * Translations: fields with the "_tid" indicate a translatable field. Translations data can be submitted
 * to the model (with the LangGrid component) and it will automatically get turned into translations.
 * 
 * Foreign Key fields: Allows a foreign key column to have a certain field of the foreign row displayed
 * searched instead of a number (often an INT key).
 * 
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
abstract class TranslatableActiveRecord extends ActiveRecord
{        
    /** @var boolean automatically translates attributes that are translatable.*/
    public $autoTranslate = true;
    
    /**
    * @inheritdoc
    */
    public function __get($name)
    {   
        if($this->autoTranslate) //&& !CONSOLE)
        {
            // If a translatable field has been requested.
            if(Yii::$app->language != Yii::$app->getBaseLanguage() && $this->hasTranslation($name))
            {
                return $this->getAttribute($name, Yii::$app->language);                
            }
        }
        
        return parent::__get($name);
    }
    
    /**
     * @return array the list of translatable attributes.
     * */
    public abstract function translatableAttributes();
    
    /**
     * @param string $attribute the name of the attribute.
     * @return boolean if the attribute can be translated.
     * */
    public function hasTranslation($attribute)
    {
        return in_array($attribute, $this->translatableAttributes()) && Yii::$app->getParameter('app.supported_languages') > 1;
    }
    
    private $_attributes;
    
    /**
     * @inheritdoc
     * */
    public function attributes()
    {
        if($this->_attributes)
        {
            return $this->_attributes;
        }
        
        $attributes = parent::attributes();
        
        foreach($this->translatableAttributes() as $attribute)
        {
            foreach(Yii::$app->getParameter('app.supported_languages') as $language)
            {
                if($language == Yii::$app->getBaseLanguage())
                {
                    continue;
                }
                
                $attributes[] = $attribute.'_'.$language;
            }
        }
            
        return $this->_attributes = array_unique($attributes);
    }
    
    /**
     * @inheritdoc
     * @param string $language the language for the attribute.
     * */
    public function getAttribute($name, $language = null)
    {
        if($language !== null && $language !== Yii::$app->getBaseLanguage())
        {
            $name .= '_'.$language;
        }

        return parent::getAttribute($name);
    }
    
    /**
     * @inheritdoc
     * @param string $language of the attribute.
     * */
    public function setAttribute($name, $value, $language = null)
    {
        if($language !== null && $language !== Yii::$app->getBaseLanguage())
        {
            $name .= '_'.$language;
        }

        parent::setAttribute($name, $value);
    }
    
    /**
     * Sets translations for an attribute.
     * @param string $name the name of the attribute
     * @param array(language => $value) the values
     * */
    public function setAttributeTranslations($name, $translations)
    {
        foreach($translations as $language => $translation)
        {
            $this->setAttribute($name, $translation, $language);
        }
    }
    
    /**
     * @inheritdoc
     * */
    public function loadDefaultValues($skipIfSet = true)
    {
        if(YII_DEBUG)
        {
            // Initialize rows that are not yet in the database.
            foreach(array_diff($this->attributes(), array_keys(static::getTableSchema()->columns)) as $attribute)
            {
                if($skipIfSet && $this->$attribute !== null)
                {
                    continue; // Skip attributes that already been set.
                }
                
                $this->$attribute =  static::getTableSchema()->columns[$this->getBaseAttribute($attribute)]->defaultValue;
            }
        }
        
        parent::loadDefaultValues($skipIfSet);
    }
    
    /**
     * Create a translation for a column.
     * @param string $name the name of the new column.
     * */
    public function createTranslationColumn($name)
    {
        $base = $this->getBaseAttribute($name);
        
        if(!in_array($base, $this->translatableAttributes())) // If this is not a translatable attribute.
        {
            return; // Let the database throw an error;
        }
        
        // If this attribute is a translation for an unsupported language.
        if(!in_array(substr($name, strlen($base) + 1), array_diff(Yii::$app->getParameter('app.supported_languages'), [Yii::$app->getBaseLanguage()])))
        {
            return; // Let the database throw an error;
        }
        
        $schema = static::getTableSchema()->getColumn($base);
        
        // Create a new column with the same name and type.
        static::getDb()->createCommand()->addColumn(static::tableName(), $name, $schema->dbType.' '.($schema->allowNull ? '': ' NOT NULL').($schema->defaultValue ? " DEFAULT \"$schema->defaultValue\"" : ''))->execute();
        // Copy table data from the base column to the new translation column.
        static::getDb()->createCommand()->setSQL('UPDATE `'.static::tableName()."` SET `$name` = `$base`;")->execute();
    }
    
    /** 
     * @inheritdoc
     * */
    public function createValidators()
    {
        // Add validators for translatable attributes.
        
        $validators = parent::createValidators();
        $languages = array_diff(Yii::$app->getParameter('app.supported_languages'), [Yii::$app->getBaseLanguage()]);
        
        foreach($validators as $validator)
        {
            $attributes = is_string($validator->attributes) ? [$validator->attributes] : $validator->attributes;
            
            foreach((is_string($validator->attributes) ? [$validator->attributes] : $validator->attributes) as $attribute)
            {
                foreach($languages as $language)
                {
                    if($this->hasAttribute($attribute.'_'.$language)) // If this is a translatable attribute.
                    {
                        $attributes[] = $attribute.'_'.$language;
                    }
                }
            }
            
            $validator->attributes = $attributes;
        }
        
        return $validators;
    }
    
    /**
     * @inheritdoc
     * */
    public function save($runValidation = true, $attributeNames = null)
    {
        /* Override of parent implementation to create columns for attribute translations
         * that do not yet exist in the database. */
        
        // Initialize rows that are not yet in the database.
        foreach(array_diff($this->attributes(), array_keys(static::getTableSchema()->columns)) as $attribute)
        {
            $this->createTranslationColumn($attribute);
        }
        
        return parent::save($runValidation, $attributeNames);
    }
    
    /**
     * @inheritdoc
     */
    protected function searchInternal($dataProvider)
    {
        $this->autoTranslate = false;
        
        $dataProvider = parent::searchInternal($dataProvider);
        
        $columns = $this->getTableSchema()->columns;
        $baseLanguage = Yii::$app->getBaseLanguage();
        
        
        $dataProvider->sort->defaultOrder = $dataProvider->sort->attributeOrders;
        $dataProvider->sort->params = [];
        
        foreach($this->translatableAttributes() as $attribute)
        {
            foreach($this->getTranslationAttributes($attribute) as $language => $translation)
            {    
                // Skip base language attribute, parent method has already added it to the seach query.
                if($language == $baseLanguage)
                {
                    continue;
                }
                
                // Add each translated attribute's condition depending on its type.
                switch($columns[$attribute]->type)
                {
                    case 'boolean':
                    case 'smallint':
                    case 'integer':
                    case 'bigint':
                    case 'float':
                    case 'decimal':
                        $dataProvider->query->orFilterWhere([$translation => $this->getAttribute($attribute, $baseLanguage)]);
                        break;
                    default: // The search is done with an SQL LIKE.
                        $dataProvider->query->orFilterWhere(['like', $translation, $this->getAttribute($attribute, $baseLanguage)]);
                }
            }
            
            // If this attribute is being sorted and a translated language is being used.
            if(isset($dataProvider->sort->defaultOrder[$attribute]) && Yii::$app->language !== Yii::$app->getBaseLanguage())
            {
                // Sort using the translated attribute.
                $dataProvider->sort->defaultOrder[$attribute.'_'.Yii::$app->language] = $dataProvider->sort->defaultOrder[$attribute];
                unset($dataProvider->sort->defaultOrder[$attribute]);
            }
        }
        
        // Regenerate the sorting order.
        $dataProvider->sort->getOrders(true);
        
        return $dataProvider;
    }
    
    /**
     * @param string $attribute the name of the attribute
     * @return array all the attributes that are translation of $attribute.
     * */
    public function getTranslationAttributes($attribute)
    {
        $attributes = [];
         
        if($this->hasTranslation($attribute))
        {
            foreach(Yii::$app->getParameter('app.supported_languages') as $language)
            {                 
                if($language == Yii::$app->getBaseLanguage())
                {
                    $attributes[$language] = $attribute;
                    continue;
                }
                
                $attributes[$language] = $attribute.'_'.$language;
            }
        }
         
        return $attributes;
    }
    
    /** @return array all the translations for an attribute.*/
    public function getTranslations($attribute)
    {    
        $translations = [];
        
        if($this->hasTranslation($attribute))
        {
            foreach(Yii::$app->getParameter('app.supported_languages') as $language)
            {             
                $translations[$language] = $this->getAttribute($attribute, $language);
            }
        }
        
        return $translations;
    }
    
    /**
     *@inheritdoc
     */
    public function getAttributeLabel($attribute)
    {
        $base = $this->getBaseAttribute($attribute);
        $label = parent::getAttributeLabel($base);

        if($attribute != $base) // If this is a translatable attribute.
        {
            $label .= ' ('.\Locale::getDisplayLanguage(substr($attribute, strlen($base) + 1), Yii::$app->language).')';
        }
        
        return $label;
    }
    
    /**
     * @inheritdoc
     * */
    public static function populateRecord($record, $row)
    {
        if(YII_DEBUG)
        {
            // Initialize rows that are not yet in the database.
            foreach(array_diff($record->attributes(), array_keys(static::getTableSchema()->columns)) as $attribute)
            {
                $record->$attribute =  static::getTableSchema()->columns[$record->getBaseAttribute($attribute)]->defaultValue;
            }
        }
        
        parent::populateRecord($record, $row);
    }
    
    /**
     * @param string $attribute the name of the attribute for which the base is wanted. 
     * @return the name of the base attribute, ie the one used when using the application's base language.
     * if there is no base attribute, the attribute is returned.
     * */
    protected function getBaseAttribute($attribute)
    {
        // If this attribute has a _, it is probably a translation..
        if(($pos = strrpos($attribute, '_')) !== false)
        {
            $base = substr($attribute, 0, $pos);
            
            if($this->hasTranslation($base))
            {
                return $base;
            }
        }
        
        return $attribute;
    }
}
