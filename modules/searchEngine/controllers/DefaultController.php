<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\searchEngine\controllers;

/**
 * The controller that conducts the search using the user submitted search query.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class DefaultController extends \yiingine\web\SiteController
{            
    /** 
     * @inheritdoc
     * */
    public function actions()
    {
        return [
            'index' => [
                'class' => '\yiingine\modules\searchEngine\base\SearchAction',
                'view' => '@app/modules/searchEngine/views/default/searchResult',
            ]
        ]; 
    }
}
