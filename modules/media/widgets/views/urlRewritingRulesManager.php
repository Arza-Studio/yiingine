<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$deleteButtonLabel = Yii::t('generic', 'Delete');
$upLabel = Yii::t('generic', 'Up');
$downLabel = Yii::t('generic', 'Down');

Yii::app()->clientScript->registerScript('urlRewritingRuleManager', <<<JS
    function UrlRewritingRuleManager(widgetId)
    {
        this.maxId = 0; //Holds the id suffix that serves to uniquely identify blocks.
        this.widgetId = widgetId;
        
        this.addRule = function(id, pattern, error, language, deleted)
        {
            this.createBlock(id, pattern, error, language);    
            this.maxId++;
        }
        
        //Generate a url rewriting rule form block.
        this.createBlock = function(id, pattern, error, language, deleted)
        {    
            var html = '';
            
            // Open Block Row (Open)
            html += '<div id="block'+this.widgetId+this.maxId+'" class="row separator">';
            // Error
            html += '<span class="error">' + error + '</span>';
            // ID hidden input.
            html += '<input id="ruleId'+this.widgetId+this.maxId+'"';
            html += ' name="UrlRewritingRules[Rule'+this.widgetId+this.maxId+'][id]"';
            html += ' type="hidden" value="' + id + '" />';
            // Delete hidden input.
            html += '<input id="toDelete'+this.widgetId+this.maxId+'"';
            html += ' name="UrlRewritingRules[Rule'+this.widgetId+this.maxId+'][delete]"';
            html += ' type="hidden" value="' + (deleted ? true: '') + '" />';
            // Language hidden input.
            html += '<input id="language'+this.widgetId+this.maxId+'"';
            html += ' name="UrlRewritingRules[Rule'+this.widgetId+this.maxId+'][languages]"';
            html += ' type="hidden" value="'+language+'" />';
            
            //Pattern input.
            html += '<table cellpadding="0" cellspacing="0">';            
            html += '<tr><td valign="top" style="vertical-align:top;width:300px;">';
            html += '<input type="text" id="pattern'+this.widgetId+this.maxId+'"'
            html += ' style="width:320px;" name="UrlRewritingRules[Rule'+this.widgetId+this.maxId+'][pattern]"';
            html += ' value="'+pattern+'" />';
            html += '</td><td valign="bottom" style="vertical-align:bottom;text-align:right;">';
            
            //Up button.
            html += '<span id=\"up'+this.widgetId +this.maxId+'\" class=\"btnFa fa fa-chevron-up\" title=\"".$upLabel."\"></span>';
            //Down button.
            html += '<span id=\"down'+this.widgetId +this.maxId+'\" class=\"btnFa fa fa-chevron-down\" title=\"".$downLabel."\"></span>';        
            
            //Delete button.
            html += '<span id="delete'+this.widgetId +this.maxId+'"';
            html += ' class="btnFa fa fa-trash" title="$deleteButtonLabel"></span>';
            html += '</td></tr>';
            html += '</table>';
            // Close Block Row (Close)
            html += '</div>';
    
            if(deleted) //If this is a deleted entry.
            {
                $("deletedUrlRewritingRules" + widgetId).append(html);
            }
            else
            {
                $("#urlRewritingRuleManager" + this.widgetId).append(html);
            }
            
            this.hookEvents(this.widgetId + this.maxId);
            
            // Re-Initialize admin content
            if(typeof initContent != 'undefined')
            {
                initContent();
            }
        }
    
        this.hookEvents = function(id)
        {                
            var widgetId = this.widgetId;
        
            $("#delete"+id).click(function()
            {
                $("#deletedUrlRewritingRules" + widgetId).append($("#block"+id).hide().remove());
                $("#toDelete" + id).val('true');
            });
        
            $('#up' + id).click(function()
            {
                var prev = $('#urlRewritingRuleManager' + widgetId).children()[$("#block" + id).index() - 1];
                if($.isEmptyObject(prev)){ return; }
                $(prev).before($("#block" + id));
            });

            $('#down' + id).click(function()
            {
                var next = $('#urlRewritingRuleManager' + widgetId).children()[$("#block" + id).index() + 1];
                if($.isEmptyObject(next)){ return; }
                $(next).after($("#block" + id));
            });
        }
    }
JS
, CClientScript::POS_HEAD);
?>
<div id="urlRewritingRuleManager<?php echo $this->id; ?>"></div>
<div id="deletedUrlRewritingRules<?php echo $this->id; ?>"></div>
<div id="addUrlRewritingRule<?php echo $this->id; ?>" style="padding-top:5px;">
    <span id="addUrlRewritingRule<?php echo $this->id; ?>Btn" class="btnFa fa fa-plus" title="<?php echo Yii::t(get_class($this).'.'.get_class($this), 'Add a new URL rewriting rule'); ?>" style="vertical-align:middle;margin-right:3px;"></span>
    <?php echo CHtml::label(Yii::t(get_class($this).'.'.get_class($this), 'Add a new URL rewriting rule'),'addUrlRewritingRule'.$this->id,array()); ?>
</div>
        
<script type="text/javascript">    
    var urlRewritingRuleManager<?php echo $this->id; ?>;
    jQuery(function()
    {
           urlRewritingRuleManager<?php echo $this->id; ?> = new UrlRewritingRuleManager("<?php echo $this->id; ?>");
        
           $("#addUrlRewritingRule<?php echo $this->id; ?>Btn").click(function(){
               urlRewritingRuleManager<?php echo $this->id; ?>.addRule(0, "", "", "<?php echo $this->language; ?>", false);
        });

        <?php foreach($rules as $rule): //Add existing rules. ?>
            urlRewritingRuleManager<?php echo $this->id; ?>.addRule("<?php echo $rule->id; ?>", "<?php echo $rule->pattern; ?>", <?php $errors = $rule->getErrors(); echo ($errors ? CJSON::encode(current(array_pop($errors))): '""'); ?>, "<?php echo $this->language?>", false);
        <?php endforeach; ?>
        
        <?php foreach($deletedRules as $rule): //Add deleted rules.?>
            urlRewritingRuleManager<?php echo $this->id; ?>.addRule("<?php echo $rule->id; ?>", "<?php echo $rule->pattern; ?>", "", "<?php echo $this->language?>", true);
        <?php endforeach; ?> 
    });
</script>
