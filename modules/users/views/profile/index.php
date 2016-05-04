<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\modules\users\parameters\Visible;

$this->title = Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'Profile');

$this->params['breadcrumbs'] = [
    ['url' => ['index'], 'label' => $this->title],
    $model->username,
];
?>
<h1 class="page-header">
    <?php echo Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), '{username}\'s Profile', ['username' => $model->username]); ?>
    <?php if(!Yii::$app->user->isGuest && $model->id == Yii::$app->user->getIdentity()->id): // If a user is viweing its own profile. ?>
        <a href="<?php echo \yii\helpers\Url::to(['edit']); ?>" class="btn btn-primary"><?php echo Yii::t('generic', 'Edit'); ?></a>
    <?php endif;?>
</h1>

    <?php 
    
    $groups = [];
    
    // Sort fields by group.
    foreach($model->getManagers() as $manager)
    {
        $field = $manager->getField();
        
        switch($field->visible)
        {
            case Visible::VISIBLE_ALL: break; // Good to go.
            case Visible::VISIBLE_ONLY_OWNER:
                // If the user is not viewing its own profile.
                if(Yii::$app->user->isGuest || $model->id != Yii::$app->user->getIdentity()->id)
                {
                    continue 2; // Skip this field.
                }
            case Visible::VISIBLE_REGISTER_USER:
                if(Yii::$app->user->isGuest) // If the user is not registered.
                {
                    continue 2; // Skip this field.
                }
            default:
                continue 2; // Fields are not visibile unless explicitely made so.
        }
        
        if(!$field->in_forms) // If the field is not suppose to be in forms.
        {
            continue;
        }
        
        // If it is the first time this group is encountered.
        if($field->formGroup && !isset($groups[$field->formGroup->name]))
        {
            // Create that group.
            $groups[$field->formGroup->name] = [
                'title' => $field->formGroup->name, 
                'elements' => [],
                'collapsed' => $field->formGroup->collapsed,
                'position' => $field->formGroup->position,
                'type' => 'group'
            ];
        }
        
        if($field->formGroup)
        {
             $groups[$field->formGroup->name]['elements'][] = $manager;   
        }
    }
    
    //Sort the form groups according to their position.
    uasort($groups, function($a, $b){ return $a['position'] - $b['position']; });
    ?>
    
    <?php if(!$groups): // If no field is visible to this user. ?>
        <?php echo Yii::t(\yiingine\modules\users\controllers\ProfileController::className(), 'This profile does not contain visible fields.'); ?>
    <?php else: ?>
        <?php foreach($groups as $group): ?>
            <div class="well">
                <h3><?php echo $group['title']; ?></h3>
                <?php foreach($group['elements'] as $element): ?>
                    <div>
                        <b><?php echo $element->getField()->title; ?> :</b> <?php echo $element->render(); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif;?>
