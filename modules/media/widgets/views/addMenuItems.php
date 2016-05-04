<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

/* This view generates the javascript and html code for the AddMenuItems widget. It allows the 
 * associating of menu items to medium.
 */
 
use \yiingine\models\MenuItem;
use \yii\helpers\Json;

// Build a list of all submenus ordered by position.
$hierarchy = [];
foreach($menuItemsList as $i => $item)
{
    $hierarchy[(int)$item->parent_id][$item->position] = [(int)$item->position, $item->name];
    ksort($hierarchy[(int)$item->parent_id]);
    
    // If there is a "last" option, make sure its position is the last one.
    if(isset($hierarchy[(int)$item->parent_id]['last']) && $hierarchy[(int)$item->parent_id]['last'][0] <= (int)$item->position)
    {
        $hierarchy[(int)$item->parent_id]['last'][0] = $item->position + 1;
    }
    else if(!isset($hierarchy[(int)$item->parent_id]['last']))
    {
        // Add a "last" option to the list of submenus.
        $hierarchy[(int)$item->parent_id]['last'] = [$item->position + 1, '*'.Yii::t('generic', 'Last').'*'];
    }
}

$id = $this->context->id;
?>

<div id="addMenuItem<?php echo $id; ?>" class="group">
    <div id="menuItems<?php echo $id; ?>"></div>
    <div class="row">
        <?php 
            // Strip tags on menu list data
            echo \yii\helpers\Html::label(Yii::t(get_class($this->context), 'Associate to a menu item'), $id, [
                'style' => 'display:block;margin:5px 0 3px 4px;'
            ]); 
            
            \yiingine\widgets\admin\MenuTree::widget([
                'id' => 'menuItemsList'.$id,
                'style' => 'vertical-align:top;',
                'side' => MenuItem::SITE
            ]);
        ?>
        <span id="addMenuItemButton<?php echo $id; ?>" class="btnFa fa fa-plus" title="<?php echo Yii::t(get_class($this->context), 'Add new item'); ?>"></span>
    </div>
</div>

<script type="text/javascript">

    function MenuItemsManager(widgetId)
    {
        this.hierarchy = <?php echo JSON::encode($hierarchy); ?>;
        
        this.maxId = 0; <?php //Holds the id suffix that serves to uniquely identify blocks. ?>
        this.widgetId = widgetId;
        
        this.addMenuItem = function(titles)
        {
            this.createBlock($("#menuItemsList" + this.widgetId).val(), 0,  $("#menuItemsList" + this.widgetId + " option:selected").text(), titles, 0, "");    
            this.maxId++;
        }
        
        <?php //Generate a menu item form block. ?>
        this.createBlock = function(parentId, menuId, parentName, titles, position, error)
        {    
            var html = '';
            
            // Open Block Row (Open)
            html += '<div id="block'+this.widgetId+this.maxId+'" class="row separator">';
            // Error
            html += '<span class="error">'+error+'</span>';
            // Input hidden ID
            html += '<input id="menuId'+this.widgetId+this.maxId+'"';
            html += ' name="AssociatedMenuItems[MenuItem'+this.widgetId+this.maxId+'][id]"';
            html += ' type="hidden" value="'+menuId+'" />';
            // Input hidden Delete
            html += '<input id="toDelete'+this.widgetId+this.maxId+'"';
            html += ' name="AssociatedMenuItems[MenuItem'+this.widgetId+this.maxId+'][delete]"';
            html += ' type="hidden" value="" />';
            // Input hidden parentID
            html += '<input id="parentId'+this.widgetId+this.maxId+'"';
            html += ' name="AssociatedMenuItems[MenuItem'+this.widgetId+this.maxId+'][parent_id]"';
            html += ' type="hidden" value="'+parentId+'" />';
            // Open Table
            html += '<table cellpadding="0" cellspacing="0">';
            // Input text Name
            html += '<tr><td valign="top" style="vertical-align:top;height:22px;">';
            html += '<label for="position'+this.widgetId+this.maxId+'"><?php echo Yii::t(get_class($this->context), 'Parent menu'); ?></label>';
            html += '</td><td valign="top" style="vertical-align:top;" colspan="2">';
            html += '<input id="name'+this.widgetId+this.maxId+'"'
            html += ' style="margin:0;width:200px;"';
            html += ' name="AssociatedMenuItems[MenuItem'+this.widgetId+this.maxId+'][parentName]"';
            html += ' readonly="readonly" size="16" type="text" value="'+parentName+'" />';
            html += '</td></tr>';
            // Input text Position
            html += '<tr><td valign="top" style="vertical-align:top;height:22px;">';
            html += '<label for="position'+this.widgetId+this.maxId+'"><?php echo Yii::t(get_class($this->context), 'Position'); ?></label>';
            html += '</td><td valign="top" style="vertical-align:top;" colspan="2">';
            html += '<select id="position'+this.widgetId+this.maxId+'"';
            html += ' style="margin:0;"';
            html += ' name="AssociatedMenuItems[MenuItem'+this.widgetId+this.maxId+'][position]"';
            html += ' type="dropdownlist">';
            for(var pos in this.hierarchy[parentId == "" ? "0" : parentId])
            {
                item = this.hierarchy[parentId == "" ? "0" : parentId][pos];
                html += '<option value="' + item[0] + '" ' + (pos == 'last' ? 'selected="selected"' : '') +'">' + item[1] + '</option>';
            }
            html += '</select>'
            html += '</td></tr>';
            
            for(lang in titles)
            {
                html += '<tr><td valign="top" style="vertical-align:top;height:22px;">';
                html += '<label for="position'+this.widgetId+this.maxId +'">' + "<?php echo Yii::t(get_class($this->context), 'Name'); ?> (" + lang + ')</label>';
                html += '</td><td valign="top" style="vertical-align:top;width:300px;">';
                html += '<input type="text" id="title'+this.widgetId+this.maxId+'"'
                html += ' style="width:320px;" name="AssociatedMenuItems[MenuItem'+this.widgetId+this.maxId+'][name_translations]['+ lang +']"';
                html += ' value="'+titles[lang]+'" />';
                html += '</td><td valign="bottom" style="vertical-align:bottom;text-align:right;">';
            }

            // Btn Delete
            html += '<span id="delete'+this.widgetId +this.maxId+'"';
            html += ' class="btnFa fa fa-trash" title="<?php echo Yii::t('generic', 'Delete'); ?>"></span>';
            html += '</td></tr>';
            // Close Table
            html += '</table>';
            // Close Block Row (Close)
            html += '</div>';
    
            $("#menuItems" + this.widgetId).append(html);
            this.hookEvents(this.widgetId + this.maxId);
            
            // Re-Initialize admin content
            if(typeof initContent != 'undefined')
            {
                initContent();
            }
        }
    
        this.hookEvents = function(id)
        {                
            $("#delete"+id).click(function()
            {
                $("#block"+id).hide()
                $("#toDelete" + id).val('true');
            });
        }
    }
</script>
<!--
<script type="text/javascript">    
    var menuItemsManager<?php echo $id; ?>;
    jQuery(function()
    {
           menuItemsManager<?php echo $id; ?> = new MenuItemsManager("<?php echo $id; ?>");
        
           $("#addMenuItemButton<?php echo $id; ?>").click(function(){
            <?php
            $titles = [];
            $currentLanguage = Yii::$app->language; //Save the current language.
            $model->autoTranslate = false;
            foreach(Yii::$app->params['app.supported_languages'] as $language)
            {
                Yii::$app->language = $language;
                $titles[$language] = str_replace("\r", '', str_replace("\n", ' ', $model->getTitle()));
            }
            $model->autoTranslate = true;
            Yii::$app->language = $currentLanguage; //Restore the language.
            ?>
            menuItemsManager<?php echo $id; ?>.addMenuItem(<?php echo JSON::encode($titles); ?>);
        });
        
           <?php 
               foreach($menuItems as $item)
               {
                   $errors = array_values($item->getErrors());
                   $parentName = $item->parent ? $item->parent->name: Yii::t('generic', 'None');
                   echo 'menuItemsManager'.$id.'.createBlock('.$item->parent_id.','.($item->isNewRecord ? '""': $item->id).', "'.MenuItem::makeNameUserFriendly($parentName).'", '.JSON::encode($item->getTranslations('name')).', '.$item->position.' ,"'.(empty($errors) ? '': $errors[0][0]).'");'."\n";
                   echo 'menuItemsManager'.$id.'.maxId++;';
               }
           ?>
           
    });
</script>
-->
