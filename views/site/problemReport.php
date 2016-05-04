<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

/* To give itself the best chances of functionning in case of a bug with the site,
 * this page uses a minimal layout. */
$this->context->layout = '@yiingine/views/layouts/main';

# TITLE
$this->title = Yii::t(\yiingine\controllers\SiteController::className(), 'Problem Report').' | '.Yii::$app->name;

\yiingine\assets\common\CommonAsset::register($this);

# CSS
$this->clear();

# BOTS PROTECTION
/* Prevent robots from indexing this page. Since registerMetaTag is called later, we cannot 
 * simply register a meta tag because it will get overriden, this is why the value is replaced
 * from within the parameters.*/
$meta = Yii::$app->getParameter('meta', []);    
$meta['robots'] = ['value' => 'NOINDEX, NOFOLLOW', 'attr' => null];    
Yii::$app->params['meta'] = $meta;

$structure = array(
    'title' => Yii::t(\yiingine\controllers\SiteController::className(), 'Problem Report'),
    'elements' => array(
        array(
            'type' => 'group',
            'title' => Yii::t(\yiingine\controllers\SiteController::className(), 'Technical Information'),
            'elements' => array(
                'browser' => array(
                    'type' => 'text',
                    'size' => 60,
                    'maxlength' => 255,
                ),
                'url' => array(
                    'type' => 'url',
                    'size' => 60,
                    'maxlength' => 255,
                ),
                'referrer' => array(
                    'type' => 'url',
                    'size' => 60,
                    'maxlength' => 255,
                ),
                'method' => array(
                    'type' => 'dropdownlist',
                    'items' => array('GET' => 'GET', 'POST' => 'POST', 'PUT' => 'PUT', 'DELETE' => 'DELETE')
                ),
                'code' => array(
                    'type' => 'text',
                    'size' => 3,
                    'maxlength' => 3,
                ),
                'message' => array(
                    'type' => 'text',
                    'size' => 60,
                    'maxlength' => 1023,
                ),
                'userId' => array(
                    'type' => 'text',
                    'size' => 9,
                    'maxlength' => 9,
                ),
                'screenHeight' => array(
                    'type' => 'text',
                    'size' => 9,
                    'maxlength' => 9,
                ),
                'screenWidth' => array(
                    'type' => 'text',
                    'size' => 9,
                    'maxlength' => 9,
                ),
                'sent' => array('type' => 'hidden')
            ),
        ),
        array(
            'type' => 'group',
            'title' => Yii::t(\yiingine\controllers\SiteController::className(), 'Description'),
            'elements' => array(
               'description' => array(
                    'type' => 'textarea',
                    'rows' => 6,
                    'cols' => 50,
                ),
                'copy' => array(
                    'type' => 'checkbox',
                ),
            )
        ),
        array(
            'type' => 'group',
            'title' => Yii::t(\yiingine\controllers\SiteController::className(), 'Contact'),
            'elements' => array(
                'email' => array(
                    'type' => 'text',
                    'size' => 60,
                    'maxlength' => 255,
                ),
                'captcha' => array(
                    'type' => '\yiingine\widgets\Captcha',
                )
            )
        ),
        \yiingine\widgets\RequiredFieldsNote::widget()
    ),
    'buttons' => array(
        'send' => array(
            'type' => 'submit',
            'label' => Yii::t(\yiingine\controllers\SiteController::className(), 'Send problem report'),
            'class' => 'btn btn-primary',
        )
    )
);

echo \yiingine\widgets\FlashMessage::display();

?>
<div id="problemReport" class="container" style="margin-top:20px;">
    <div class="col-lg-8 col-lg-offset-2">
        <?php 
            $form = \yiingine\widgets\ActiveForm::begin([
                'id' => 'ProblemReport-form',
                'enableAjaxValidation' => false,
                'enableClientValidation' => false,
            ]);
            
            echo $form->formStructure($model, $structure);
            
            \yiingine\widgets\ActiveForm::end();
        ?>
    </div>
</div>
