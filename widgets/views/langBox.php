<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use \yiingine\widgets\LangBox;

// The alternate language choice to display on the button.
if(count($options) > 2)
{
    $buttonLanguage = Yii::$app->language; // The button language is the current language.
}
else
{
    // The button language is the language not in use.
    $buttonLanguage = array_keys($options); 
    $buttonLanguage = $buttonLanguage[0] != Yii::$app->language ? $buttonLanguage[0] : $buttonLanguage[1];
}

switch($this->context->displayMode)
{
    case LangBox::NAME:
        $buttonLanguage = ucfirst(\locale_get_display_language($buttonLanguage, $buttonLanguage));
        break;
    case LangBox::CODE:
        // Nothing to do, the button language is already the language code..
        break;
    default:
        throw new \yii\base\InvalidParamException('Invalid display mode!');
}

switch($switchType)
{
    case LangBox::LINKS: //Language choices are to be displayed as links side by side.
        $i = 0; //To find the last element of the list.
        foreach($options as $language => $url)
        {
            $languageName = ucfirst(\locale_get_display_language($language, $language));
            
            $class = ($currentLanguage == $language) ? 'active' : '' ;
            
            echo Html::a(
                $displayMode == LangBox::NAME ? $languageName : $language, 
                $url,
                ['title' => $languageName, 'class' => $class.' hidden-xs']
            );
            // Add a separator between languages.
            if($separator !== false && ++$i < count($options))
            {
                echo $separator;
            }
        }
    break;
    case LangBox::SELECT: // Language choices are to be displayed as a drop down list.
        $selected = $options[Yii::$app->language];
        $options = array_flip($options);
        if($displayMode == LangBox::NAME)
        {
            foreach($options as $url => &$language) // Replace the language codes with their name.
            {
                $language = ucfirst(\locale_get_display_language($language, $language));
            }
        }
        //$options = array_flip($options);
        echo Html::dropDownList('langBox'.$this->context->id, $selected, $options, $htmlOptions = [
            'onchange' => 'window.location = $(this).prop("value");',
            'class' => 'noMonitoringForChanges form-control hidden-xs'
        ]);
    break;
    case LangBox::DROPDOWN: // Language choices are to be displayed as a bootstrap button and dropdown menu.
        ?>
        <div class="dropdown hidden-xs form-group">
            <button class="btn btn-primary" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?= $buttonLanguage; ?></button>
            <ul class="dropdown-menu pull-right">
                <?php foreach($options as $language => $url): 
                    $class = '';
                    $style = '';
                    // If this language is disabled.
                    if(!in_array($language, Yii::$app->params['app.available_languages']))
                    {
                        $class .= ' adminOverlay disabled';
                        $style .= 'position: relative';
                    }
                    ?>
                    <li><a href="<?php echo $url; ?>" class="<?php echo $class; ?>" style="<?php echo $style; ?>"><?php echo ucfirst(\locale_get_display_language($language, $language)); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    break;
    case LangBox::MODAL: // Language choices are to be displayed as a bootstrap button and dropdown menu.
        ?>
        <button class="btn btn-primary form-group" data-toggle="modal" data-target="#langBoxModal" role="button" aria-expanded="false"><?= $buttonLanguage; ?></button>
        <?php
    break;
} 
// Mobile
if($switchType != LangBox::MODAL):
?>
<button class="btn btn-primary visible-xs form-group" data-toggle="modal" data-target="#langBoxModal" role="button" aria-expanded="false"><?= $buttonLanguage; ?></button>
<?php endif; ?>
