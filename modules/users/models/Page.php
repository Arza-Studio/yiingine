<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\users\models;

/**
 * The page class for the users module.
 */
class Page extends \app\modules\media\models\Page
{
    /**
     * @inheritdoc
     * */
    public static function getViews()
    {
        return [
            [
                'title' => ['en' => '2 Columns', 'fr' => '2 Colonnes'],
                'description' => [
                    'en' => 'The content of the users module is displayed in the left column while the widgets and the associated media are displayed on the right column.',
                    'fr' => 'Vue sur 2 colonnes. Le module utilisateurs est affiché dans la colonne de gauche tandis que les outils et les media associés sont affichés dans la colonne de droite'
                ],
                'path' => '@yiingine/modules/users/views/layouts/2columns.php'
            ],
               [
                  'title' => ['en' => '1 Column', 'fr' => 'défaut'],
                'description' => ['en'=> 'View on one column without widgets and associated media.', 'fr' => 'Vue sur 1 colonne sans widgets ni media associés.'],
                 'path' => '@yiingine/modules/users/views/layouts/1column.php'
               ]
        ];
    }
}
