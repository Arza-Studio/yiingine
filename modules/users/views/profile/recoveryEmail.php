<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$recoveryUrl = \yii\helpers\Url::to($recoveryUrl, true);

?>
<?php if(Yii::$app->language == 'fr'): ?>

    <h1><?php echo Yii::$app->name; ?> - Récupération de mot de passe</h1>
    <p>Pour récupérer votre mot de passe, veuillez cliquer sur le lien suivant: <a href="<?php echo $recoveryUrl; ?>"><?php echo $recoveryUrl; ?></a>.
    Si le lien n'est pas fonctionnel, ouvrez votre navigateur et copiez le dans la barre d'adresse.</p>
    <p>L'équipe de <?php echo Yii::$app->name; ?></p>
    
<?php else: // Default is english. ?>

    <h1><?php echo Yii::$app->name; ?> - Password recovery</h1>
    <p>To recover your password, please click on the following link: <a href="<?php echo $recoveryUrl; ?>"><?php echo $recoveryUrl; ?></a>.
    If the link does not work, open your browser and copy it in the address bar.</p>
    <p>The <?php echo Yii::$app->name; ?> team</p>
      
<?php endif; ?>
