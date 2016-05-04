<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;

/**
 * AssetBundle for the Index medium.
 * */
class IndexAsset extends \yii\web\AssetBundle
{
    /** 
     * @inheritdoc 
     * */
    public $sourcePath = '@app/modules/media/assets/media/index';
    
    /** 
     * @inheritdoc 
     * */
    public $css = ['default.css'];
}
IndexAsset::register($this);

$this->title = Yii::t('generic', 'Home');

if($model->background)
{
    Yii::$app->params['background'] = $model->getManager('background')->getFileUrl($model);
}

$this->params['breadcrumbs'] = [$this->title];

?>
<div id="index" class="corpus">
    <?php
    if($model->associated_index_gallery): ?>
        <?= $this->render('@app/modules/media/views/media/gallery/_slider.php',[
            'sliderId' => 'indexSlider',
            'model' => $model->associated_index_gallery[0],
            'embedResponsive' => false,
            'allowExpansion' => false,
            'lazyLoad' => false,
        ]);?>
    <?php endif; ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="jumbotron">
                    <h1><?= $model->getTitle(true); ?></h1>
                    <?= $this->render('@app/modules/media/views/media/page/_content.php', [
                        'model' => $model, 
                        'variables' => $variables,
                        'lazyLoad' => false,
                        'attribute' => 'index_content'
                    ]);?>
                </div>
            </div>
        </div>
        <div id="indexMedia">
            <?php 
            if(isset($model->associated_media))
            {
                // Administrators are allowed to see all associated media.
                $relation = !Yii::$app->user->isGuest && !Yii::$app->user->getIdentity()->superuser ? 'all_associated_media' : 'associated_media';
                $media = $model->$relation;
                switch(count($media))
                {
                    case 1: $class = 'col-sm-12'; break;
                    case 2: $class = 'col-sm-6'; break;
                    case 3: $class = 'col-sm-4'; break;
                    case 4: $class = 'col-sm-6'; break;
                    case 5: $class = 'col-sm-4'; break;
                    default: $class = 'col-sm-4'; break;
                }
                
                for($row = 0; $row < 2; $row++)
                {
                    echo '<div class="row row-eq-height">';
                    
                    for($column = 0; $column < 3; $column++)
                    {
                        if(($i = (3 * $row) + $column) > 5)
                        {
                            break 2; // Do not allow more than 6 media.
                        }

                        echo Html::tag('div', \yiingine\modules\media\widgets\Thumbnail::widget(['imageLazyLoad' => false, 'model' => $media[$i]]), ['class' => $class]);
                    }
                    
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
</div>
