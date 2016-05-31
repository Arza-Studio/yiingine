<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Html;
use \yii\helpers\Url;
use rmrevin\yii\fontawesome\FA;

if(Yii::$app->request->isAjax)
{
    $this->context->layout = null; // Do not render the layout during ajax requests.
}

echo $this->render('_common', ['model' => $model]);

$this->params['breadcrumbs'][] = Yii::t('generic', 'Manage');

# Warning box
if(isset($displayWarning) && $displayWarning === true): ?>
    <div class="panel panel-warning">
        <div class="panel-heading"><h3 class="panel-title"><?php echo Yii::t('generic', 'Warning').' !'; ?></h3></div>
        <div class="panel-body"><?php echo Yii::t(\yiingine\web\admin\ModelController::className(), 'An uninformed modification to the values displayed below could break the site. Please inform your webmaster before you make any modification to those values.'); ?></div>
    </div>
<?php endif; ?>

<?php

if(!isset($dataProvider))
{
    $dataProvider = $model->search(Yii::$app->request->queryParams);
}
if(!isset($noDisplayColumn))
{
     $noDisplayColumn = [];
}

// These columns only apply to active records.
if($model instanceof \yii\db\ActiveRecord) 
{
    # Id column
    array_unshift($columns, [
        'attribute' => 'id',
        'headerOptions' => [
            'width' => 30,
            'style' => 'text-align:center;padding-right:0px;',
        ],
        'options' => [
            'style' => 'text-align:center;',
            'width' => 30,
            'style' => 'min-width: 30px'
        ]
    ]);
    
    # Enable column
    // If there is an enabled column for this model.
    if($model instanceof \yii\db\ActiveRecord && $model->hasAttribute('enable') && !in_array('enable', $noDisplayColumn))
    {
        $columns[] = [
            'attribute' => 'enable',
            'class' => '\yiingine\grid\BooleanColumn'
        ];
    }
    
    # Timestamp updated column
    // If there is a ts_updt column for this model.
    if($model->hasAttribute('ts_updt') && !in_array('ts_updt', $noDisplayColumn)) 
    { 
        // Add it automatically.
        $columns[] = [ 
            'attribute' => 'ts_updt',
            'headerOptions' => [
                'width' => 80,
                'class' => 'timestampColumn',
            ],
            'contentOptions' => [
                'class' => 'timestampColumn',
            ],
        ];
    }
}

# Buttons column
// Build the template for the buttons according to the user's permissions.
$template = $this->context->checkAccess('update') ? '{update}': '{view}';
$template .= $this->context->checkAccess('delete') ? '{delete}': '';
$template .= '{link}';

$this->params['linkButton'] = isset($linkButton) && $linkButton ? $linkButton: 'false';
$this->params['deleteVisible'] = isset($deleteVisible) && $deleteVisible ? $deleteVisible: 'true';

Yii::$app->session->addFlash(\yiingine\widgets\FlashMessage::SUCCESS, [
    'id' => 'deleteSuccessFlashMessage',
    'type' => \yiingine\widgets\FlashMessage::SUCCESS,
    'message' => Yii::t(\yiingine\web\admin\ModelController::className(), 'The item was successfully deleted.'),
    'slideUp' => false, // Cannot slide the message up because it is not displayed right away.
    'options' => ['style' => 'display:none;']
]);

Yii::$app->session->addFlash(\yiingine\widgets\FlashMessage::DANGER, [
    'id' => 'deleteErrorFlashMessage',
    'type' => \yiingine\widgets\FlashMessage::DANGER,
    'message' => Yii::t(\yiingine\web\admin\ModelController::className(), 'There was an error while deleting the item.'),
    'slideUp' => false, // Cannot slide the message up because it is not displayed right away.
    'options' => ['style' => 'display:none;']
]);


$this->registerJs('
function deleteModel(url, key, button)
{
    if(confirm("'.Yii::t('yii', 'Are you sure you want to delete this item?').'"))
    {
        if($("#deleteErrorFlashMessage:visible"))
        {
            $("#deleteErrorFlashMessage").hide();
        }        
        if($("#deleteSuccessFlashMessage:visible").length)
        {
            $("#deleteSuccessFlashMessage").hide();
        } 
        
        // Loader here.
        $.ajax({
            type: "POST",
            url: url,
            success: function()
            { 
                $("#deleteSuccessFlashMessage").fadeIn();
                $(button).parent().parent().fadeOut(300, function(){ $(this).remove(); })
            },
            error: function()
            {     
                $("#deleteErrorFlashMessage").fadeIn();        
            }
        });
    }
};        
', \yii\web\View::POS_HEAD);

// Adds the control column to the GridView.
$columns[] = [
    'class' => 'yii\grid\ActionColumn',
    'buttons' => [
        'update' => function($url, $model, $key) 
        {
            $queryParams = Yii::$app->request->queryParams;
            unset($queryParams['_pjax']);
            return !isset($updateVisible) || $updateVisible ? Html::a(FA::icon('pencil'), Url::to(array_merge(['update', 'id' => $model->id], $queryParams)), ['data-pjax' => 0, 'class' => 'btn btn-primary btn-sm', 'title' => Yii::t('generic', 'Update')]) : '';
        },
        'delete' => function($url, $model, $key)
        {
            $queryParams = Yii::$app->request->queryParams;
            unset($queryParams['_pjax']);
            $deleteVisible = eval('return '.$this->params['deleteVisible'].';');
            return $deleteVisible ? Html::a(FA::icon('trash'), Url::to(array_merge(['delete', 'id' => $model->id], $queryParams)), ['data-pjax' => 0, 'class' => 'btn btn-danger btn-sm', 'title' => Yii::t('generic', 'Delete'), 'onclick' => 'deleteModel("'.$url.'", '.$key.', this); return false;']) : '';
        },
        'view' => function($url, $model, $key)
        {
            $queryParams = Yii::$app->request->queryParams;
            unset($queryParams['_pjax']);
            return !isset($viewVisible) || $viewVisible ? Html::a(FA::icon('eye'), Url::to(array_merge(['view', 'id' => $model->id], $queryParams)), ['data-pjax' => 0, 'class' => 'btn btn-primary btn-sm', 'title' => Yii::t('generic', 'View')]) : '';
        },
        'link' => function($url, $model, $key)
        {
            $linkButton = eval('return '.$this->params['linkButton'].';');
            return $linkButton ? Html::a(FA::icon('external-link'), $linkButton, ['data-pjax' => 0, 'class' => 'btn btn-primary btn-sm', 'title' => Yii::t(\yiingine\web\admin\ModelController::className(), 'View in site')]) : '';
        }
    ],
    'template' => $template,
    'headerOptions' => [
        'width' => isset($linkButton) && $linkButton ? 110 : 70,
        'class' => 'buttonColumn',
    ],
    'contentOptions' => [
        'class' => 'buttonColumn',
    ],
];

// Render the admin GridView view
echo $this->render('//admin/model/_adminGridView', array(
    'columns' => $columns,
    'model' => $model,
    'dataProvider' => $dataProvider,
));
