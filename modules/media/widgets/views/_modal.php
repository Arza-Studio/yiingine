<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use \yii\helpers\Url;
use \yii\web\View;
use rmrevin\yii\fontawesome\FA;
use \yiingine\modules\media\widgets\Modal;

// To make access to variables easier.
extract(get_object_vars($this->context));
$id = $this->context->id;

?>
<div id="<?= $this->context->id ?>" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php 
            # Header
            if(strpos($layout, '{header}') !== false): ?>
            <div class="modal-header">
                <?php
                if(isset($layoutItems['{header}']))
                {
                    $headerHtml = $layoutItems['{header}'];
                }
                else
                {
                    $headerHtml = Html::tag('button', '<span aria-hidden="true">&times;</span>', [
                        'type' => 'button',
                        'class' => 'close',
                        'data-dismiss' => 'modal',
                        'aria-label' => Yii::t('generic', 'Close')
                    ]);
                    $headerHtml .= Html::tag($headerTag, $model->getTitle(), ['class' => 'modal-title']);
                }
                echo $headerHtml;
                ?>
            </div>
            <?php endif; ?>
            <div class="modal-body container-fluid">
                <?php
                $strings = [
                    '{header}' => '',
                    '{footer}' => ''
                ];
                # Content
                if(strpos($layout, '{content}') !== false)
                {
                    $contentHtml = $model->getContent();
                    if($contentHtml != '')
                    {
                        if($contentLazyLoad) // If the img and iframe tags in content must be lazy loaded.
                        {
                            $contentHtml = \yiingine\widgets\LazyLoad::widget([
                                'html' => $contentHtml,
                            ]);
                        }
                        if($contentOptimizeImgs) // If the image must be optimized.
                        {
                            $optimizeImgsParams = [
                                'html' => $contentHtml,
                                'unwrapParagraph' => true,
                                'options'=> [
                                    'class' => 'img-responsive'
                                ]
                            ];
                            if($contentOptimizeImgs === Modal::RATIO_1BY1 ||
                               $contentOptimizeImgs === Modal::RATIO_4BY3 ||
                               $contentOptimizeImgs === Modal::RATIO_16BY9)
                            {
                                $optimizeImgsParams['options'] = [
                                    'class' => 'embed-responsive-item',
                                    'style'=>'object-fit:cover;'
                                ];
                                $optimizeImgsParams['layout'] = Html::tag('p', '{img}', [
                                    'class' => 'embed-responsive embed-responsive-'.$contentOptimizeImgs
                                ]);
                            }
                            $contentHtml = \yiingine\widgets\OptimizeImgs::widget($optimizeImgsParams);
                        }
                        if($contentOptimizeIframes) // If the image must be optimized.
                        {
                            $optimizeIframesParams = [
                                'html' => $contentHtml
                            ];
                            if($contentOptimizeIframes === Modal::RATIO_1BY1 ||
                               $contentOptimizeIframes === Modal::RATIO_4BY3 ||
                               $contentOptimizeIframes === Modal::RATIO_16BY9)
                            {
                                $optimizeIframesParams['options'] = [
                                    'class' => 'embed-responsive-item',
                                    'style'=>'object-fit:cover;'
                                ];
                                $optimizeIframesParams['layout'] = Html::tag('p', '{iframe}', [
                                    'class' => 'embed-responsive embed-responsive-'.$contentOptimizeIframes
                                ]);
                            }
                            $contentHtml = \yiingine\widgets\OptimizeIframes::widget($optimizeIframesParams);
                        }
                        $strings['{content}'] = Html::tag('div', $contentHtml, ['class' => 'content']);
                    }
                    else
                    {
                       $strings['{content}'] = '';
                    }
                }
                
                # Additional items
                if(!empty($layoutItems))
                {
                    foreach($layoutItems as $item => $html)
                    {
                        // Replace {header} and {footer} with empty string cause there position is forced.
                        if($item == '{header}' || $item == '{footer}')
                        {
                            $strings[$item] = '';
                        }
                        else
                        {
                            $strings[$item] = $html;
                        }
                    }
                }                
                echo strtr($layout, $strings);
                ?>
            </div>
            <?php 
            # Footer
            if(strpos($layout, '{footer}') !== false): ?>
            <div class="modal-footer">
                <?php
                if(isset($layoutItems['{footer}']))
                {
                    $footerHtml = $layoutItems['{footer}'];
                }
                else
                {
                    $footerStrings = [];
                    // Close
                    if(strpos($footerLayout, '{close}') !== false)
                    {
                        $footerCloseHtml = Html::tag('button', Yii::t('generic', 'Close'), [
                            'type' => 'button',
                            'class' => 'btn btn-primary',
                            'data-dismiss' => 'modal',
                            'aria-label' => Yii::t('generic', 'Close')
                        ]);
                        $footerStrings['{close}'] = $footerCloseHtml;
                    }
                    // Share links
                    if(strpos($footerLayout, '{share}') !== false)
                    {
                        $footerShareHtml = \yiingine\widgets\ShareBox::widget([
                            'type' => \yiingine\widgets\ShareBox::BUTTONS,
                            'url' => Url::to($model->getUrl(), true),
                            'title' => $model->getTitle(),
                            'description' => $model->getDescription()
                        ]);
                        $this->registerJs('
                        $(".modal .share .btn").not(".hoverBehaviourInitialized").hover(
                            function(){
                                $(this).css({background:$(this).data("color"),color:"white"});
                            },
                            function(){
                                $(this).removeAttr("style");
                            }
                        ).addClass("hoverBehaviourInitialized");
                        ', View::POS_READY);
                        $footerStrings['{share}'] = $footerShareHtml;
                    }
                    // Additional footer items
                    if(!empty($footerLayoutItems))
                    {
                        foreach($footerLayoutItems as $item => $html)
                        {
                            $footerStrings[$item] = $html;
                        }
                    }
                    $footerHtml = strtr($footerLayout, $footerStrings);
                }
                echo $footerHtml;
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
