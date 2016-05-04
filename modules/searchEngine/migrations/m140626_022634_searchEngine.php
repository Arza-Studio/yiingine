<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\models\UrlRewritingRule;

/** Represents a database migration of m140626_022634_searchEngine.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> */
class m140626_022634_searchEngine extends \yiingine\console\DbMigration
{    
    /** Applies the logic to be executed when applying the migration.
     * @return boolean if the migration can be applied. */
    public function up()
    {
        echo "    > creating URL rewriting rules ...";
        $time = microtime(true);
        
        $search = ['en' => 'search', 'fr' => 'rechercher'];
        
        // Find the last position of all URL rewriting rules.
        if(!($position = UrlRewritingRule::find()->select(['position'])->orderBy('position DESC')->one()))
        {
            $position = 0;
        }
        else
        {
            $position = $position->position + 1;
        }
        
        // Create an url rewriting rule for each language.
        foreach(Yii::$app->getParameter('app.supported_languages') as $language)
        {
            $rule = $this->addEntry(new UrlRewritingRule(), [
                'languages' => $language,
                'pattern' => '/'.(isset($search[$language]) ? $search[$language]: 'search').'/<query:.*>',
                'route' => '/searchEngine/search',
                'defaults' => '["searchEngine" => "searchEngine", "language" => "'.$language.'"]',
                'position' => $position++
            ]);
        }
        
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
    }
    
    /** Applies the logic to be executed when removing a migration.
     * @return boolean if the migration can be removed.*/
    public function down()
    {
        echo "    > removing URL rewriting rules ...";
        $time = microtime(true);
        
        UrlRewritingRule::deleteAllWithEvents(['route' => '/searchEngine/search']);
        
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
    }
}
