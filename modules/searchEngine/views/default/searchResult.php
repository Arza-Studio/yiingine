<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\libs\Functions;
use \yii\helpers\Html;

/** Wrap with span.highlight the terms found in the given string.
 * @param string $string the text to highlight.
 * @param string $term the term to match with a case and accents unsensitive regex.
 * @param boolean $sentencesOnly to return only the sentences where the term has been found.
 * @return string the highlighted string.
 * */
function highlightResult($string, $term, $sentencesOnly = false)
{
    // If we want only the sentences where the term has been found we start with
    // an empty string otherwise we start with the given string
    $hightlightedString = ($sentencesOnly) ? '' : $string ;
    // The regular expression is selected according to $sentencesOnly
    $term = str_replace(' ', '|', $term); // replace space with pipe for multiple words request (ex : Lorem ipsum => Lorem|ipsum)
    $term = $termLowerCase = mb_strtolower($term); // string to lower case (ex : Lorem|ipsum => lorem|ipsum)
    foreach(preg_split('/\|/', $termLowerCase) as $t) $term .= '|'.ucwords($t); // string to title case (ex : lorem|ipsum => lorem|ipsum|Lorem|Ipsum)
    $term .= '|'.mb_strtoupper($termLowerCase); // string to upper case (ex : lorem|ipsum|Lorem|Ipsum => lorem|ipsum|Lorem|Ipsum|LOREM|IPSUM)
    if($term != Functions::stripAccents($term)) $term .= '|'.Functions::stripAccents($term); // add terms without accents if necessary
    $regexTerms = '/'.$term.'/';
    $regexTermsWithSentence = "/[A-Za-z0-9]*[^\.;\?\!]*(".$term.")[^\.;\?\!]*/"; 
    $regex = $sentencesOnly ? $regexTermsWithSentence : $regexTerms ;
    if($count = preg_match_all($regex, $string, $matches))
    {
        foreach($matches[0] as $key => $match) // For each term found in the given string
        {
            // In case of $sentencesOnly the sentence is incremented to the returned string
            // To prevent bugs in request with several words, the hightlighting if operate with $regexTerms for each sentence found through the $regexTermsWithSentence
            if($sentencesOnly)
            {
                foreach($matches[0] as $sentence)
                {
                    preg_match_all($regexTerms, $sentence, $matchesInSentence);
                    foreach($matchesInSentence[0] as $key => $matchInSentence)
                    {
                        $sentence = str_replace($matchInSentence, '<span class="highlight">'.$matchInSentence.'</span>', $sentence);
                    }
                    $hightlightedString .= $sentence.' [...] ';
                }
            }
            // Otherwise we just wrap the terms found in the given string
            else
            {
                $hightlightedString = str_replace($match, '<span class="highlight">'.$match.'</span>', $hightlightedString);
            }
        }
    }
    
    return array($hightlightedString, $count);
}

$this->beginContent('@app/views/layouts/main.php');

$this->params['breadcrumbs'] = [Yii::t(\yiingine\modules\searchEngine\controllers\DefaultController::className(), 'Search result for "{query}"', ['query' => $query])];

?>
<div class="container">
    <?php // TITLE & DETAILS ?>
    <div class="page-header">
        <h1><?php echo Yii::t(\yiingine\modules\searchEngine\controllers\DefaultController::className(), 'Search result for "{query}"', ['query' => $query]); ?></h1>
        <?php if($result->totalCount !== 0)
        {
            echo Functions::pickPlural(Yii::t(\yiingine\modules\searchEngine\controllers\DefaultController::className(), '{count} result was found.|{count} results were found.', ['count' => $result->totalCount]), $result->totalCount > 1); 
        } ?>
    </div>
    
    <?php if($result->totalCount === 0): ?>
        <div class="alert alert-info">
            <?php echo Yii::t(\yiingine\modules\searchEngine\controllers\DefaultController::className(), 'The request returned no results.'); ?>
        </div>
    <?php else: ?>

    <?php foreach($result->getModels() as $item):
    
        # Admin Overlay
        /*\yiingine\widgets\admin\AdminOverlay::widget([
        'selector' => '.default[data-id="'.$id.'"] .container',
        'model' => $item[1]
        ]);*/
    
    ?>
        <div class="container well">
            <div class="col-lg-3 col-md-4 col-sm-4">
                <?php
                // If their is no thumbnail set for this model we try to get the default one
                $thumbnail = ($item[1]->getThumbnail()) ? $item[1]->getThumbnail() : (Yii::$app->getParameter("yiingine.SocialMetas.default_thumbnail") ? Yii::$app->baseUrl."/user/assets/".Yii::$app->getParameter("yiingine.SocialMetas.default_thumbnail") : false );
                
                if($thumbnail)
                {
                    echo  Html::a(Html::img(
                        Yii::$app->imagine->getThumbnail(str_replace(Yii::$app->request->baseUrl, Yii::getAlias('@webroot'), \yii\helpers\Url::to($thumbnail)), 160),
                        [
                            'alt' => $item[1]->getTitle(false),
                            'title' => $item[1]->getTitle(false)
                        ]
                    ), $item[1]->getUrl());
                }
                ?>
            </div>
            <div class="col-lg-9">
                <?php
                // Title
                $highlightedTitle = highlightResult($item[1]->getTitle(false), $query);
                echo Html::tag('h2', Html::a($highlightedTitle[0].' ('.$item[1]->getModelLabel().')', $item[1]->getUrl()));
    
                // Content
                $content = strip_tags($item[1]->getContent());
                $highlightedContent = highlightResult($content, $query, true);
    
                echo Html::tag('p', \yiingine\widgets\Truncate::widget([
                    'html' => $content,
                    'length' => 500,
                    'removeFormating' => false
                ]));
    
                // Highlight the other searchable attributes.
                foreach($item[2] as $key => $searchableAttribute):
    
                    // DIRTY !!! : The two first $searchableAttribute are the equivalent of getTitle() and getContent() so we jump it
                    if($key <= 1)
                    {
                        continue;
                    }
                    
                    $highlightedValue = highlightResult($item[1]->$searchableAttribute, $query);
                    if($highlightedValue[1]):?>
                        <p><b><?php echo $item[1]->getAttributeLabel($searchableAttribute); ?> :</b> <?php echo $highlightedValue[0]; ?></p>
                    <?php endif;
                endforeach; ?>
                <?php echo Html::a(Yii::t('generic', 'Display'), $item[1]->getUrl(), ['class' => 'btn btn-primary', 'style' => 'float:right;']); ?>
            </div>
        </div>
    <?php endforeach; ?>
    <div style="text-align:center;">
        <?= \yii\widgets\LinkPager::widget([
            'pagination' => $result->pagination,
            'maxButtonCount' => 15,
            'nextPageLabel' => '&raquo;',
            'prevPageLabel' => '&laquo;',
            'firstPageLabel' => Yii::t('generic', 'First'),
            'lastPageLabel' => Yii::t('generic', 'Last'),
            'options' => ['class' => 'pagination pagination-lg']
        ]); ?>
    </div>
    <?php endif; ?>
</div>
<?php $this->endContent(); ?>
