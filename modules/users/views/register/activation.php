<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Url;

$activationUrl = Url::to($activationUrl, true);

if(Yii::$app->language == 'fr'): ?>

    <h1><?php echo Yii::$app->name; ?> - Activation</h1>
    <p>Merci de vous être inscrits à <?php echo Yii::$app->name; ?>.<br/> 
    Pour compléter la création de votre
    compte utilisateur, veuillez cliquer sur le lien suivant: <a href="<?php echo $activationUrl; ?>"><?php echo $activationUrl; ?></a>.
    Si le lien n'est pas fonctionnel, ouvrez votre navigateur et copiez le dans la barre d'adresse.</p>
    <p>L'équipe de <?php echo Yii::$app->name; ?></p>
    
<?php else: //Default is english. ?>

    <h1><?php echo Yii::$app->name; ?> - Activation</h1>
    <p>Thank you for subscription to <?php echo Yii::$app->name; ?>.<br/> 
    To complete the creation of your user account, please click on the following link: <a href="<?php echo $activationUrl; ?>"><?php echo $activationUrl; ?></a>.
    If the link does not work, open your browser and copy it in the address bar.</p>
    <p>The <?php echo Yii::$app->name; ?> team</p>
<?php endif; ?>
