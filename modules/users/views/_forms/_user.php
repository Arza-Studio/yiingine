<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use yiingine\modules\users\models\User;

$roles = []; // Will contain a list of the available roles.

// If RBAC is enabled and the current user is an Administrator.
if(Yii::$app->getParameter('enable_auth_management') && Yii::$app->user->can('Administrator'))
{
    // Build the list of roles associated with this user.
    $modelRoles = $model->isNewRecord ? []: Yii::$app->authManager->getRolesByUser($model->id);
    $model->roles = [];
    foreach($modelRoles as $role)
    {
        $model->roles[] = $role->name;
    }

    foreach(Yii::$app->authManager->roles as $role) //Populate the roles list data.
    {
        if($model->id == Yii::$app->user->getIdentity()->id && $role->name == 'Administrator') // If an administrator is modifying his own account.
        {
            continue; // Cannot self-revoke an Administrator role.
        }
        
        $roles[$role->name] = '<b>'.$role->name.'</b> ('.$role->description.')';    
    }
}

$profileInputs = \yiingine\modules\customFields\managers\Base::renderInputs($model);

return [
    'type' => 'form', 
    'elements' => [
        'user' => [
            'type' => 'fieldset',
            'title' => User::getModelLabel(),
            'elements' => [
                [
                    'title' => Yii::t(\yiingine\modules\users\models\User::className(), 'Credentials'),
                    'type' => 'group',
                    'elements' => [
                        'username' => [
                            'type' => 'text',
                            'style' => 'width:50%;',
                            'maxlength' => 20,
                            'visible' => $model->isAttributeSafe('username')
                        ],
                        'password' => [
                            'type' => 'password',
                            'style' => 'width:70%;',
                            'maxlength' => 128
                        ],
                        'verifyPassword' => [
                            'type' => 'password',
                            'style' => 'width:70%;',
                            'maxlength' => 128
                        ],
                       'email' => [
                               'type' => 'text',
                            'style' => 'width:70%;',
                            'maxlength' => 128,
                            'visible' => $model->isAttributeSafe('email')
                        ],
                    ]
                ],
                 [
                    'title' => Yii::t('generic', 'Controls'),
                    'type' => 'group',
                    'elements' => [      
                        'activation_key' => [
                            'type' => 'text',
                            'size' => 60,
                            'maxlength' => 128,
                            'forceVisible' => YII_DEBUG && Yii::$app->controller->getSide() == \yiingine\web\Controller::ADMIN && $this->context->adminDisplayMode >= \yiingine\models\admin\AdminParameters::ADVANCED_DISPLAY_MODE,
                        ],
                        'superuser' => [
                            'type' => 'checkbox',
                            'layout'=> "{input} {label}\n{hint}\n{error}"
                        ],
                        'status' => [
                            'type' => 'dropdownlist',
                            'items' =>  [
                                User::STATUS_NOACTIVE => User::getStatusLabel(User::STATUS_NOACTIVE),
                                User::STATUS_ACTIVE => User::getStatusLabel(User::STATUS_ACTIVE),
                                User::STATUS_BANNED => User::getStatusLabel(User::STATUS_BANNED),  
                            ], 
                            'prompt' => Yii::t('generic', 'Select an item'),
                        ],
                    ],
                    'visible' => !in_array($model->scenario, ['userEdit', 'registration']) 
                ], // end group
            ], // end groups
        ],
        'profile' => [
            'type' => 'fieldset',
            'title' => Yii::t(\yiingine\modules\users\models\User::className(), 'Profile'),
            'elements' => $profileInputs[1],
            'visible' => !empty($profileInputs)
        ],
        'rbac' => [
            'type' => 'fieldset',
            'title' => Yii::t(\yiingine\modules\users\models\User::className(), 'Roles'),
            'visible' => Yii::$app->controller instanceof \yiingine\gridController && Yii::$app->getParameter('enable_auth_management') && Yii::$app->user->can('Administrator'),
            'elements' => [
                (boolean)$roles ? 
                [
                    'title' => Yii::t(\yiingine\modules\users\models\User::className(), 'Roles'),
                    'type' => 'group',
                    'visible' => (boolean)$roles,
                    'elements' => [
                        'roles' => [
                            'type' => 'checkboxlist',
                            'items' => $roles,
                            'encode' => false
                        ]
                    ]
                ] : Yii::t(\yiingine\modules\users\models\User::className(), 'No roles defined.')
            ]
        ]
    ]
];
