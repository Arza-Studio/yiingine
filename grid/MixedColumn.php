<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\grid;

use \Yii;

/**
 * A GridViewColumn that allows many different column types (all inheriting from
 * GridColumn) to be displayed in the same cell.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class MixedColumn extends yii\grid\Column
{
    /**
     * @var array the columns to mix, columns can be given as a string for the
     * name of class, in which case it will be instantiated and configurated like
     * MixedColumn, otherwise they can be given as arrays, in which case they will be
     * configured using the data in the array.
     */
    public $columns = [];
    
    /** @var array the columns that have filtering enabled.*/
    protected $filteredColumns = [];
    
    /** 
     * @inheritdoc
     * */
    public function init()
    {    
        parent::init();
        
        foreach($this->columns as &$column)
        {
            if(is_string($column)) //If column is a string, it could either be a class or a separator.
            {
                if(class_exists($column)) // If the string is an actual class.
                {
                    $column = ['class' => $column];
                }
                else // The string is a separator.
                {
                    continue; // Leave the column as is.
                }
            }
            
            if(is_array($column)) //If column is given as array.
            {
                $column = Yii::createObject(array_merge($column, [
                    'class' => isset($column['class']) ? $column['class'] : $this->grid->dataColumnClass ? : \yii\grid\DataColumn::className(),
                    'grid' => $this->grid
                ]));
            }
            // Else column is assumed to be an instance of yii\grid\Column and rendered as is.
            
            if(!$column->visible)
            {
                continue;
            }
            
            /* In order to display itself correctly, the yii\grid\GridView needs to exactly know
             * how much columns it is managing to adjust its col spans. Thus, a dummy
             * column must be added for each child.*/
            $dummyColumn = new DummyColumn();
            $dummyColumn->grid = $this->grid;
            $this->grid->columns[] = $dummyColumn;
            
            if($column instanceof \yii\grid\DataColumn && $column->filter !== false) // If filtering is enabled for this column.
            {
                $this->filteredColumns[] = $column;
            }
        }
        if(count($this->columns))
        {
            // Remove a dummy column because the mixed column counts as one.
            array_pop($this->grid->columns);
        }
    }
    
    /**
     * @inheritdoc
     */
    public function renderHeaderCell()
    {   
        /*
         * Override of parent implementation to prevent the mixed column for rendering its own
         * header cell and render the header cell of its children instead.
         */
        
        $value = '';
        
        foreach($this->filteredColumns as $column)
        {   
            // If no header html option is set : set headerHtmlOption with width=80px.
            if(empty($column->headerOptions))
            {
                $column->headerOptions = ['width' => 80]; 
            }
            $value .= $column->renderHeaderCell();
        }
        return $value;
    }
    
    /**
     * @inheritdoc
     */
    public function renderFilterCell()
    {  
        /* Override of parent implementation to prevent the mixed column for rendering its own
         * filter cell. */
        
        $value = '';
        
        foreach($this->filteredColumns as $column)
        {   
            // If no header html option is set : set headerHtmlOption with width=100px.
            if(!isset($column->filterOptions))
            {
                $column->filterOptions = ['width' => 80]; 
            }
            $value .= $column->renderFilterCell();
        }
        return $value;
    }
    
    /**
     * @inheritdoc
     */
    public function renderDataCell($model, $key, $index)
    {  
        // Override of parent implementation to span the columns.
        $this->contentOptions['colspan'] = count($this->filteredColumns);
        return parent::renderDataCell($model, $key, $index);
    }
    
     /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $value = '';
        foreach($this->columns as $column)
        {
            if(is_string($column)) // If the column is a separator.
            {
                $value .= $column;
            }
            else // Let the column render itself.
            {
                $value .= $column->renderDataCellContent($model, $key, $index);
            }
        }
        return $value;
    }
}

/** This column does not do anything, it just exists to force GridView to correctly
 * set its colspans.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> */
class DummyColumn extends yii\grid\Column
{
    /**
     * @inheritdoc
     * */
    public function renderHeaderCell(){}
    
    /**
     * @inheritdoc
     * */
    public function renderFooterCell(){}
    
    /**
     * @inheritdoc
     * */
    public function renderFilterCell(){}
    
    /**
     * @inheritdoc
     * */
    public function renderDataCell($model, $key, $index){}
}
