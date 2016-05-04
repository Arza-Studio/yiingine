<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace app\modules\media\models;

use \Yii;

/**
 * The model class for Page media.
 */
class Page extends \yiingine\modules\media\models\Medium
{
    /**
     * @inheritdoc
     * */
    public static $includeInSiteMap = true;
    
    /**
    * @inheritdoc
    */
    public static function getModelLabel($plural = false)
    {
        return Yii::tA(['en' => '{n, plural, =1{Page}other{Pages}}', 'fr' => '{n, plural, =1{Page}other{Pages}}'], ['n' => $plural ? 2 : 1]);
    }    
    
    /**
     * @inheritdoc
     * */
    public static function find()
    {
        $query = parent::find();
        
        // Use the generic medium model to look for different classes of Page.
        $query->modelClass = \yiingine\modules\media\models\Medium::className();
        $query->prepareCallback = function($query)
        {    
            $pageClasses = [self::className()];
            
            foreach(Yii::$app->getModules() as $module)
            {
                if($module instanceof \yiingine\modules\media\components\Module && $module->enableModuleModel)
                {
                    $pageClasses[] = strpos('\\', $module->moduleModelClass) === 0 ? substr($module->moduleModelClass, 1) :$module->moduleModelClass;
                }
            }
            
            $query->andWhere(['type' => $pageClasses]);
        };
        
        return $query;
    }
    
    /** 
     * @inheritdoc
     * */
    public function beforeDelete()
    {
        // If this page is owned by a module and the module model is enabled.
        if($this->module_owner_id && Yii::$app->getModule($this->module_owner_id)->enableModuleModel)
        {
            throw new \yii\base\Exception('Cannot delete a page owned by a module!');
        }
        
        return parent::beforeDelete();
    }
    
    /**
     * @inheritdoc
     * */
    public static function getViews()
    {
        return [
            [
                'title' => ['en' => '2 Columns (default)', 'fr' => '2 Colonnes (par défaut)'],
                'description' => ['en' => 'Displays the title and the content of the page in the left column and associated objects on the right column.', 'fr' => 'Affiche le titre et contenu de la page dans la colonne de gauche et les objets associés dans la colonne de droite.'],
                'path' => '/media/page/2columns',
            ],
            [
                'title' => ['en' => '1 Column', 'fr' => '1 Colonne'],
                'description' => ['en' => 'Displays the title and the content of the page using all available space.<br />The associated objects to the right column are not displays.', 'fr' => 'Affiche le titre et le contenu de la page en se servant de tout l\'espace disponible.<br />Les objects associés à la colonne de droite ne sont pas affichés.'],
                'path' => '/media/page/1column',
            ],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public static function getSearchableAttributes()
    {
        return ['page_title', 'page_content', 'keywords', 'description', 'type', 'id'];
    }
}
