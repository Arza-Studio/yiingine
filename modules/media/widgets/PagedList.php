<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\widgets;

use \Yii;

/**
 * A widget that renders a list of media.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 * */
class PagedList extends Renderer
{
    /**
     * @var array|false a configuration for a Pagination object or false if pagination
     * should be disabled.
     * */
    public $pagination = ['pageSize' => 10];
    
    /**
     * @var array|false a configuration for a Sort object or false if psorting
     * should be disabled.
     * */
    public $sort = false;
    
    /**
     * @var yii\db\QueryInterface a query object for fetching models;
     * */
    public $query;
    
    /**
     * @var array rendering parameters to give to the line view.
     * */
    public $lineViewParameters = [
        'layout' => '<div class="container-fluid"><div class="row row-eq-height"><div class="col-sm-4">{image}</div><div class="col-sm-8">{header}{description}{footer}</div></div></div>',
        'descriptionTruncate' => 300,
        'line' => true
    ];
    
    /**
     * @var string the view to use when rendering a line.
     * */
    public $lineView = '_thumbnail';
    
    /** 
     * @var string the view that renders the widget.
     * */
    public $viewName = '_list';
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        parent::init();
        
        PagedListAsset::register($this->view);
        
        $this->parameters['dataProvider'] = new \yii\data\ActiveDataProvider([
            'query' => $this->query,
            'sort' => $this->sort,
            'pagination' => $this->pagination
        ]);
    }
}

/**
 * The asset bundle for the Thumbnail widget.
 * */
class PagedListAsset extends \yii\web\AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@yiingine/modules/media/widgets/assets/';
    
    /** @inheritoc */
    public $css = ['pagedList/_list.css'];
    
    public $depends = [
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
    ];
}
