<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace app\modules\media\models;

/**
 * The model class for Index media.
 */
class Index extends \yiingine\modules\media\models\Medium
{            
    /**
     * @inheritdoc
     * */
    public static $singleton = true;
    
    /**
     * @inheritdoc
     * */
    public static $includeInSiteMap = true;
    
    /**
     * @inheritdoc
     */
    public static function getModelLabel($plural = false)
    {
        return \Yii::tA(['en' => 'Index', 'fr' => 'Accueil']);
    }
    
    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return preg_replace("/\{\{(.+)\}\}/", "", str_replace(["{{gallery}}", "{{/gallery}}"], '', $this->index_content));
    }
    
    /**
     * @inheritdoc
     * */
    public static function getViews()
    {
        return [
            [
                'path' => '/media/index/default',
            ]
        ];
    }
    
    /**
     * @inheritdoc
     */
    public static function getSearchableAttributes()
    {
        return ['index_title', 'index_content', 'keywords', 'description', 'type'];
    }
}
