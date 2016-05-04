<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\media\widgets;

use \Yii;

/**
 * A widget for displaying media using a named view. If found, this widget will
 * use the view within the medium's view folder. 
 * Otherwise, it will use a generic view.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 * */
class Renderer extends \yii\base\Widget
{
    /**
     * @var \yiingine\modules\media\models\Medium the medium to render as a thumbnail.
     * */
    public $model;
    
    /**
     * @var string the name of the view to use.
     * */
    public $viewName;
    
    /**
     * @var boolean force rendering using the generic view.
     * */
    public $forceGenericView = false;
    
    /**
     * @var array extra parameters to pass to the view.
     * */
    public $parameters = [];
    
    /**
     * @inheritdoc
     * */
    public function init()
    {
        parent::init();
        
        if(!$this->model)
        {
            throw new \yii\base\InvalidParamException('Missing $model attribute!');
        }
        else if(!($this->model instanceof \yiingine\modules\media\models\Medium))
        {
            throw new \yii\base\InvalidParamException(get_class($this->model).' is not a Medium');   
        }
    }
    
    /**
     * @inheritdoc.
     * */
    public function run()
    {
        if(!$this->forceGenericView)
        {
            try // Attemps to render the widget using a named view provided by the Medium.
            {
                return $this->render(
                    '@app/modules/media/views/media/'.lcfirst($this->model->formName()).'/'.$this->viewName.'.php',
                    $this->parameters
                );
            }
            catch (\yii\base\InvalidParamException $e)
            {
            }
        }
        
        // The name view file does not exist for that Medium, use a generic view.
        return $this->render($this->viewName, get_object_vars($this));
    }    
}
