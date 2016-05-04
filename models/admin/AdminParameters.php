<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\models\admin;

use \Yii;

/**
* A model class for managing parameters for the administration panel and the site.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class AdminParameters extends \yii\base\Model implements \yiingine\db\DescriptiveInterface
{        
    /** @var integer the display mode of the admin. The administration panel can be advanced mode (0)
     * which shows all pages or in normal mode, which hides certain sensitive pages. */
    const NORMAL_DISPLAY_MODE = 0;
    const ADVANCED_DISPLAY_MODE = 1;
    public $displayMode = self::NORMAL_DISPLAY_MODE;
    
    /**
     * @param integer $mode the mode to convert.
     * @return string the label of the mode.
     * */
    public static function getDisplayModeLabel($mode)
    {
        switch($mode)
        {
            case self::NORMAL_DISPLAY_MODE: return Yii::t(__CLASS__, 'standard');
            case self::ADVANCED_DISPLAY_MODE: return Yii::t(__CLASS__, 'advanced');
            default: return 'Invalid display mode';
        }
    } 
    
    /**
     * @rinheritdoc.
     */
    public function rules()
    {
        return [
            ['displayMode', 'boolean'],
        ];
    }
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        // Admin parameters only work when using an AdminController.
        if(CONSOLE || !(Yii::$app->controller instanceof \yiingine\gridController))
        {
            return;
        }
        
        $this->displayMode = Yii::$app->controller->adminDisplayMode;
        
        parent::init();
    }
    
    /** 
     * Apply the admin parameters.
     * */
    public function apply()
    {
        if(CONSOLE)
        {
            return;
        }
        
        // Set a cookie to save the current display mode.    
        Yii::$app->response->cookies->add(new \yii\web\Cookie([
            'name' => 'adminDisplayMode',
            'expire' => 0,
            'value' => $this->displayMode
        ]));
        
        // Admin parameters only work when using an AdminController.
        if(!(Yii::$app->controller instanceof \yiingine\gridController))
        {
            return;
        }
        
        Yii::$app->controller->adminDisplayMode = $this->displayMode;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'displayMode' => Yii::t(__CLASS__, 'Administration panel display mode'),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeDescriptions()
    {
        return [
            'displayMode' => Yii::t(__CLASS__, 'The administration interface can be displayed in two modes: <ul><li>the <b>standard mode</b>, which displays functions most useful for editing the site, </li><li> the <b>advanced mode</b>, which displays all menus, including those that require an advanced knowledge of the system.</li></ul>'),
        ];
    }
    
    /** 
     * @inheritdoc
     * */
    public function getAttributeDescription($attribute)
    {
        $descriptions  = $this->attributeDescriptions();
         
        return isset($descriptions[$attribute]) ? $descriptions[$attribute] : '';
    }
}
