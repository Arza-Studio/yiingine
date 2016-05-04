<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

/**
 * This view is used by yiingine\modules\media\migrations/m000000_000001_media.php
 * to create the media module's model classes.
 * The following variables are available in this view:
 * @var $className string the model's class name.
 * */

echo "<?php\n";
?>

namespace yiingine\modules\media\models;

use \Yii;

/**
 * Model class for <?= $className ?>.
 */
class <?= $className ?> extends \yiingine\modules\media\Medium
{
    /**
     * @inheritdoc
     * */
    public static function find()
    {
        return static::find()->where(['type' => '<?= $className ?>']);
    }
    
    /**
     * @inheritdoc
     * */
    public function __construct($config = [])
    {
        // Override of parent constructor to set the type name.
        
        parent::__construct('<?= $className ?>', 'media', $config);
    }
}
