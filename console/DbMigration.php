<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\console;

use \Yii;

/**
 * Extends yii's db migration with helper functions for inializing models.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
abstract class DbMigration extends \yii\db\Migration
{        
    /** 
     * @var Module the module being migrated, null if no module.
     * */
    public $module = null;
    
    /** Adds an entry for a model. Translations can be passed through $data as
     * $lang => $value and will be automatically insterted depending on what
     * languages are supported.
     * @param ActiveRecord $model an instance of the model to be filled with data.
     * @param array $data the data to add to the model.
     * @return ActiveRecord the added entry.*/
    public function addEntry($model, $data)
    {
        if($model instanceof \yiingine\db\TranslatableActiveRecord)
        {
            foreach($data as $name => $value)
            {
                $fillOtherTranslations = [];
                
                // If $value has translations.
                if(is_array($value) && $model->hasTranslation($name))
                {
                    $model->setAttributeTranslations($name, $value);
                    
                    $fillOtherTranslations = array_diff(Yii::$app->getParameter('app.supported_languages'), array_keys($value));
                }
                else
                {
                    $model->$name = $value; // Set data normally.
                    
                    if($model->hasTranslation($name))
                    {
                        // If no value was specified for other translations.
                        $fillOtherTranslations = array_diff(Yii::$app->getParameter('app.supported_languages'), [Yii::$app->getBaseLanguage()]);
                    }
                }
                
                if($fillOtherTranslations)
                {
                    // Fill translations that have not been set with the values in the source language.
                    foreach($fillOtherTranslations as $language)
                    {
                        $model->setAttribute($name, $model->getAttribute($name), $language);
                    }
                }
            }
        }
        else
        {
            foreach($data as $name => $value)
            {
                $model->$name = $value;
            }
        }
        
        if(!$model->save()) //If saving failed.
        {
            $class = get_class($model);
            foreach($model->getErrors() as $attribute => $error) //Iterate through all errors to display them.
            {
                echo "    > Error for $attribute of class $class : {$error[0]} \n";
                dump($model->$attribute);
            }
            throw new \yii\base\Exception(); //Just to print the stack.
            exit(1); //Cannot continue;
        }
        
        return $model;
    }
    
    /** Copy a list of files to their new destination.
     * @param array $files array(source => destination).*/
    public function copy($files)
    {
        echo "    > copying files ...";
        $time = microtime(true);
        
        foreach($files as $source => $destination)
        {    
            $dir = str_replace(basename($destination), '', $destination); // Remove the filename from directory. 
            if(!is_dir($dir)) // Create the missing directories in the path.
            {
                mkdir($dir, 0755, true);    
            }
            copy($source, $destination);
        }
        
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
    }
    
    /** Creates a RBAC permission.
     * @param string $name the name of the permission.
     * @param string $description the description of the permission. 
     * */
    public function createPermission($name, $description)
    {
        $permission = Yii::$app->authManager->createPermission($name);
        $permission->description = $description;
        
        Yii::$app->authManager->add($permission);
        
        return $permission;
    }
    
    /** Creates a RBAC role.
     * @param string $name the name of the role.
     * @param string $description the description of the role. 
     * */
    public function createRole($name, $description)
    {
        $role = Yii::$app->authManager->createRole($name);
        $role->description = $description;
        
        Yii::$app->authManager->add($role);
        
        return $role;
    }
    
    /** Create default permissions for models.
     * @param string $module the name of the module for which permissions are added.
     * @param array $models the list of models.*/
    public function createModelPermissions($module, $models)
    {
        $authManager = Yii::$app->authManager;
    
        //Create a role for the module if it does not exist yet.
        if(!$authManager->getRole(ucfirst($module).'Module-manage'))
        {
            $moduleManager = $authManager->createRole(ucfirst($module).'Module-manage');
            $moduleManager->description = Yii::tA(['en' => 'Manage the '.$module.' module', 'fr'=> 'Gestion du module '.$module]);
            $authManager->add($moduleManager);
            $authManager->addChild($authManager->getRole('Administrator'), $moduleManager);
        }
    
        foreach($models as $model) // Create an operation set for each model.
        {
            // If a manage role for this model has been created already.
            if($authManager->getRole($model.'-manage'))
            {
                continue; // Skip it because we do not want to erase any modifications.
            }
    
            if(@class_exists($model) && in_array('yiingine\db\ModelInterface', class_implements($model))) // If there is class for the model.
            {
                $label = $model::getModelLabel(true); // Get the human friendly label.
            }
            else
            {
                $label = $model; // Else use the model name.
            }
            
            $task = $this->createPermission($model.'-manage', Yii::tA(array('en' => 'Manage '.$label, 'fr' => 'Gestion de '.$label)));
            $authManager->addChild($task, $this->createPermission($model.'-create', Yii::tA(array('en' => 'Create '.$label, 'fr' => 'Création de '.$label))));
            $authManager->addChild($task, $this->createPermission($model.'-update', Yii::tA(array('en' => 'Update '.$label, 'fr' => 'Modification de '.$label))));
            $authManager->addChild($task, $this->createPermission($model.'-view', Yii::tA(array('en' => 'View '.$label, 'fr' => 'Consultation de '.$label))));
            $authManager->addChild($task, $this->createPermission($model.'-delete', Yii::tA(array('en' => 'Delete '.$label, 'fr' => 'Supression de '.$label))));

            $authManager->addChild($moduleManager, $task);
        }
    }
    
    /** Delete default permissions for models.
     * @param string $module the name of the module for which permissions are deleted.
     * @param array $models the list of models.*/
    public function deleteModelPermissions($module, $models)
    {
        $authManager = Yii::app()->authManager;
    
        $authManager->removeAuthItem(ucfirst($module).'Module-manage');
    
        foreach($models as $model) //Remove the operation set for each model.
        {    
            $authManager->removeAuthItem($model.'-create');
            $authManager->removeAuthItem($model.'-update');
            $authManager->removeAuthItem($model.'-view');
            $authManager->removeAuthItem($model.'-delete');
            $authManager->removeAuthItem($model.'-manage');
        }
    }
    
    /** If the migration applies to this system or not. Migrations that do not applied will
     * not be listed by the EngineMigrateCommand.
     * @return boolean if the migration applies */
    public function applies()
    {
        return true;
    }
    
    /**
     * Generates an arbitraruy quantity of lorem ipsum text.
     * @param string $language leave null to use the current language.
     * @param integer $titleLevels precede paragraphs with titles.
     * @param integer $paragraphCount number of paragraphs to return.
     * @param boolean $list include a list.
     * @param boolean $justText return only text without any html tag.
     * @return string the generated lorem ipsum.
     */
    public static function getLoremIpsum($language = null, $paragraphCount = 1, $titleLevels = 0, $list = false, $justText = false)
    {
        // Use current language if no language was specified.
        $language = $language === null ? Yii::app()->language : $language;
        
        $html = '';
        if($paragraphCount >= 1)
        {
            $html .= '<p><strong>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam ut dapibus nulla. Etiam hendrerit mauris et metus gravida aliquam a rutrum libero. Nunc et justo in lacus gravida laoreet ac quis metus. Aliquam '.Yii::t(__CLASS__, 'keyword').'1 erat volutpat. Praesent varius vehicula felis. Pellentesque ornare dictum mauris vitae dictum. Nullam rutrum <em>felis in mauris <u>pulvinar nec pharetra</u></em> diam mattis.</strong> Nulla auctor elit id risus ultricies sed tincidunt augue cursus. In hac habitasse platea dictumst. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. <em>Nullam volutpat <u>vehicula diam et ullamcorper.</u></em> Donec eleifend urna et tortor bibendum eu blandit metus vehicula. '.ucfirst(Yii::t(__CLASS__, 'keyword')).'2 suspendisse neque quam, sollicitudin sed porta facilisis, rhoncus in orci. Vivamus eu felis sit amet odio sollicitudin viverra. Sed pulvinar sapien nec libero rhoncus quis pretium leo elementum. Morbi et neque eros <a href="#" title="link">link</a>.</p>'."\n";
        }

        if($paragraphCount >= 2)
        {
            if($titleLevels >= 2 && !$justText) 
            {
                $html .= Yii::t(__CLASS__, '<h{level}>Level {level} Title</h{level}>', ['level' => 2], $language)."\n";
            }
    
            if($titleLevels >= 3 && !$justText)
            {
                $html .= Yii::t(__CLASS__, '<h{level}>Level {level} Title</h{level}>', ['level' => 3], $language)."\n";
            }
    
            $html .= '<p>Sed pretium venenatis nulla, id egestas mi condimentum eget. Duis in imperdiet nunc. Proin eu arcu vulputate sapien adipiscing fermentum. Aliquam lectus dolor, congue quis accumsan at, lacinia nec diam. Donec enim ipsum, ultrices nec fermentum sed, feugiat nec tellus. Maecenas rhoncus nunc in magna auctor sodales at faucibus risus. Curabitur eget sem vel urna '.mb_strtoupper(Yii::t(__CLASS__, 'keyword')).'3 cursus feugiat. Curabitur vitae risus ut augue commodo aliquam ut vitae tellus. Ut convallis enim at dolor venenatis at laoreet erat congue. Donec consectetur sodales ligula, iaculis lacinia est iaculis in. Integer diam orci, fringilla non accumsan quis, faucibus et dolor. Praesent sed quam libero, eu posuere.</p>'."\n";
        }
        // List
        if($list)
        {
            $html .= '<ul>'."\n";
            $html .= '<li><strong>Pellentesque habitant morbi tristique senectus et netus et <em>malesuada fames ac <u>turpis</u> egestas.</em></strong> Nullam volutpat vehicula diam et ullamcorper. Donec eleifend urna et tortor</li>'."\n";
            $html .= '<li>Quisque volutpat, dolor id tincidunt luctus, odio ipsum molestie est, et blandit risus elit ut tellus. Integer tristique, quam sit amet placerat convallis.</li>'."\n";
            $html .= '<li>Praesent sed quam libero, eu posuere.</li>'."\n";
            $html .= '<li>Nulla auctor elit id risus ultricies sed tincidunt augue cursus. In hac habitasse platea dictumst. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nullam volutpat vehicula diam et ullamcorper. Donec eleifend urna et tortor bibendum eu blandit metus vehicula. Suspendisse neque quam, sollicitudin sed porta facilisis.</li>'."\n";
            $html .= '</ul>'."\n";
        }
        if($paragraphCount >= 3)
        {
            if($titleLevels >= 3 && !$justText) 
            {
                $html .= Yii::t(__CLASS__, '<h{level}>Level {level} Title</h{level}>', array('{level}' => 3), null, $language)."\n";
            }
    
            $html .= '<p>In hac habitasse platea dictumst. Quisque volutpat, dolor id tincidunt luctus, odio ipsum molestie est, et blandit risus elit ut tellus. Integer tristique, quam sit amet placerat convallis, dolor elit luctus ante, non iaculis lorem orci sit amet nisl. Vestibulum posuere porttitor turpis, id malesuada justo imperdiet eu. Sed at ipsum tortor. Sed pretium '.Yii::t(__CLASS__, 'keyword').'1 venenatis nulla, id egestas mi condimentum eget. Duis in imperdiet nunc. Proin eu arcu vulputate sapien adipiscing fermentum. Aliquam lectus dolor, congue quis accumsan at, lacinia nec diam. Donec enim ipsum, ultrices nec fermentum sed, feugiat nec tellus. Maecenas rhoncus nunc in magna auctor sodales at faucibus risus. Curabitur eget sem vel urna cursus feugiat. Curabitur vitae risus ut augue commodo aliquam ut vitae tellus. Ut convallis enim at dolor venenatis at laoreet erat congue. Donec consectetur sodales ligula, iaculis lacinia est iaculis in. Integer diam orci, fringilla non accumsan quis, faucibus et dolor. Praesent sed quam libero, eu posuere.</p>'."\n";
        }
        if($paragraphCount >= 4)
        {
            if($titleLevels >= 4 && !$justText) 
            {
                $html .= Yii::t(__CLASS__, '<h{level}>Level {level} Title</h{level}>', array('{level}' => 4), null, $language)."\n";
            }
            $html .= '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam ut dapibus nulla. Etiam hendrerit mauris et metus gravida aliquam a rutrum libero. Nunc et justo in lacus gravida '.Yii::t(__CLASS__, 'keyword').'3 laoreet ac quis metus. Aliquam erat volutpat. Praesent varius vehicula felis. Pellentesque ornare dictum mauris vitae dictum. Nullam rutrum felis in mauris pulvinar nec pharetra diam mattis. Nulla auctor elit id risus ultricies sed tincidunt augue cursus. In hac habitasse platea dictumst. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. '.Yii::t(__CLASS__, 'keyword').'2 Nullam volutpat vehicula diam et ullamcorper. Donec eleifend urna et tortor bibendum eu blandit metus vehicula. Suspendisse neque quam, sollicitudin sed porta facilisis, rhoncus in orci. Vivamus eu felis sit amet odio sollicitudin viverra. Sed pulvinar sapien nec libero rhoncus quis pretium leo elementum. Morbi et neque eros.'."\n";
        }
    
        if($justText)
        {
            $html = strip_tags($html);
            $html = str_replace(' link', '', $html);
            $html = str_replace(' lien', '', $html);
        }
        
        return $html;
    }
}
