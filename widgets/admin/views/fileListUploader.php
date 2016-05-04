<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yii\helpers\Url;
use \yiingine\libs\Functions;
use \yii\web\View;

/* This view generates a standalone block of html code with an associated script that lets the user
select and upload files of any type. When the multi file option is set, other files
can be uploaded by adding other blocks and their order of storage can be modified through
moving them up or down realtive to one another.*/

$fileUploaderSizeLimit = Functions::returnBytes(ini_get('post_max_size'));
$fileUploaderTempDirectory = Url::to(['/user/temp']);
$noFilesFoundMessage = Yii::t(get_class($this->context), 'No files found');
$deleteMessage = Yii::t('generic', 'Delete');
$addMessage = Yii::t('generic', 'Add');
$upMessage = Yii::t('generic', 'Up');
$downMessage = Yii::t('generic', 'Down');
$typeErrorMessage = Yii::t(get_class($this->context), '{file} has invalid extension. Only {extensions} are allowed.');
$sizeErrorMessage = Yii::t(get_class($this->context), '{file} is too large, maximum file size is {sizeLimit}.');
$minSizeErrorMessage = Yii::t(get_class($this->context), '{file} is too small, minimum file size is {minSizeLimit}.');
$emptyErrorMessage = Yii::t(get_class($this->context), '{file} is empty, please select files again without it.');
$onLeaveMessage = Yii::t(get_class($this->context), 'One or more files are being uploaded, if you leave now the upload will be cancelled.');    
$uploadMessage = Yii::t(get_class($this->context), 'Upload a file');
$cancelMessage = Yii::t(get_class($this->context), 'Cancel');
$failedMessage = Yii::t(get_class($this->context), 'Failed');
$baseUrl = \yiingine\assets\admin\AdminAsset::register($this)->baseUrl;

//Javascript class for managing a file uploader.
$this->registerJs("
function FileListUploader(widgetId, options)
{
    this.maxId = 0; // Holds the id suffix that serves to uniquely identify blocks.
    this.widgetId = widgetId; // Holds the identifier for the widget associated with this class.
    this.maxNumberOfFiles = 1; // The maximum number of files that can be uploaded.
    this.uploader = null; // The uploader widget.
    this.options = options; // Extra options for the file uploader.

    // Iterates through the blocks in order to refresh the file list (the hidden field).
    this.refreshFileList = function()
    {
        var files = '';
        var names = $('.fileName' + this.widgetId);
        for(var i = 0; i < names.length; i++)
        {
            if(\$(names[i]).html().length > 0)
            {    
                files += \$(names[i]).html();
                if(i < names.length - 1)
                {
                    files += ',';
                }
            }
        }
        $('#fileListField' + this.widgetId).attr('value', files);
    }

    // Generate the file icon or image preview in a file upload block.
    this.createFilePreview = function(extension,filePath,id,fileName)
    {
        html = '';
        
        switch(extension)
        {
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'png':
            case 'svg':
                html += '<a href=\"'+filePath+'\" class=\"zoombox\"  title=\"'+fileName+'\" >';
                ". // Append a random argument to the image to prevent the browser from caching it.
                "html += '<img id=\"imagePreview' + this.widgetId +id+'\" width=\"100\" src=\"'+filePath+'".'?'.Functions::generateRandomString(8, false, false, true)."\" alt=\"'+fileName+'\" />';
                html += '</a>';
                break;
            case 'aac':
            case 'ai':
            case 'aiff':
            case 'avi':
            case 'bmp':
            case 'c':
            case 'cpp':
            case 'css':
            case 'dat':
            case 'dmg':
            case 'doc':
            case 'docx':
            case 'dotx':
            case 'dwg':
            case 'dfx':
            case 'eps':
            case 'exe':
            case 'flv':
            case 'h':
            case 'hpp':
            case 'html':
            case 'ics':
            case 'iso':
            case 'java':
            case 'key':
            case 'mid':
            case 'mp3':
            case 'mp4':
            case 'mpg':
            case 'odf':
            case 'ods':
            case 'odt':
            case 'otp':
            case 'ott':
            case 'pdf':
            case 'php':
            case 'ppt':
            case 'pptx':
            case 'psd':
            case 'py':
            case 'qt':
            case 'rar':
            case 'pdf':
            case 'rb':
            case 'rtf':
            case 'sql':
            case 'tga':
            case 'tgz':
            case 'tiff':
            case 'txt':
            case 'wav':
            case 'xls':
            case 'xlsx':
            case 'xml':
            case 'yml':
            case 'zip':
                html += '<a href=\"'+filePath+'\" target=\"_blank\">';
                html += '<img id=\"imagePreview' + this.widgetId +id+'\" width=\"100\" src=\"".$baseUrl."/images/icons/'+extension+'.png\" alt=\"'+filePath+'\" class=\"icon\" />';
                html += '</a>';
                break;
            case '':  // No file defined yet.  
                html += '<img id=\"imagePreview' + this.widgetId +id+'\" width=\"100\" src=\"".$baseUrl."/images/noFiles.jpg\" alt=\"".$noFilesFoundMessage."\" />';
                break;
            default:
                html += '<a href=\"'+filePath+'\" target=\"_blank\">';
                html += '<img id=\"imagePreview' + this.widgetId +id+'\" width=\"100\" src=\"".$baseUrl."/images/icons/_blank.png\" alt=\"'+filePath+'\" class=\"icon\" />';
                html += '</a>';
                break;
                // Nothing
        }
        return html;
    }
    
    /* Generate a file upload block with its associated control and events and returns it.
    id: the id of the block. 
    fileName: the name of the file. Leave empty if block is blank.
    filePath: the url to the file for preview. Leave empty if block is blank. */
    this.createBlock = function(id, fileName, filePath)
    {
        var html = '';

        // FILE LIST UPLOADER BLOCK
        html += '<div id=\"block' + this.widgetId + id+'\" class=\"block fileListUploaderBlock\">';

            // COLUMN 1
            html += '<div class=\"FLUBcolumn1\">';
                // Icon
                var extension = fileName.substr(fileName.lastIndexOf('.') + 1).toLowerCase(); //console.log('extension : '+extension);
                html += this.createFilePreview(extension,filePath,id,fileName);
            html += '</div>';

            // COLUMN 2
            html += '<div class=\"FLUBcolumn2\">';
                // ROW 1
                html += '<div class=\"FLUBcolumn2row1\">';
                    // File Uploader Btn
                    html += '<div id=\"fileUploader' + this.widgetId +id+'\" class=\"fileUploader\"></div>';
                    html += '<div class=\"FLUBclear\"></div>';
                html += '</div>';
                // ROW 2
                html += '<div class=\"FLUBcolumn2row2\">';
                    // File name + File Path Container
                    html += '<div class=\"fileNamePath\">';
                        // File name
                        html += '<span id=\"fileName' + this.widgetId +id+'\" class=\"fileName'+this.widgetId+'\">'+fileName+'</span>';
                        // File path
                        ".
                        // Only display the full path of the image if we the user is within the admin.
                        (Yii::$app->controller->getSide() === \yiingine\web\Controller::ADMIN ?
                            "html += '<a id=\"filePath' + this.widgetId +id+'\" class=\"filePath\" href=\"'+filePath+'\" target=\"_blank\">'+filePath+'</a>';" :
                            '')
                    ."html += '</div>';
                    // Btns (Delete, Add, Up, Down)
                    html += '<div class=\"FLUBbtns\">';
                        html += '<i id=\"delete' + this.widgetId +id+'\" class=\"btnFa fa fa-trash\" title=\"".$deleteMessage."\"></i>';
                        if(this.maxNumberOfFiles > 1)
                        {
                            html += '<span id=\"up' + this.widgetId +id+'\" class=\"btnFa fa fa-chevron-up\" title=\"".$upMessage."\"></span>';
                            html += '<span id=\"down' + this.widgetId +id+'\" class=\"btnFa fa fa-chevron-down\" title=\"".$downMessage."\"></span>';
                            html += '<span id=\"add' + this.widgetId +id+'\" class=\"btnFa fa fa-plus\" title=\"".$addMessage."\"></span>';
                        }
                    html += '</div>';
                html += '</div>';
            html += '</div>';

            // CLEAR
            html += '<div class=\"FLUBclear\"></div>';

        html += '</div>';

        return html;
    }    

    this.initBlock = function(blockID)
    {
        //console.log('initBlock(blockID=' + this.widgetId +blockID+') !');
        // Column 2 Resizing
        var imgHeight = $('#block' + this.widgetId +blockID).find('img').outerHeight(); //console.log('imgHeight = '+imgHeight);
        var col2Height = $('#block' + this.widgetId +blockID).find('.FLUBcolumn2').outerHeight(); //console.log('col2Height = '+col2Height);
        if(imgHeight>col2Height) $('#block' + this.widgetId +blockID).find('.FLUBcolumn2').css('height',imgHeight+'px');
        else $('#block' + this.widgetId +blockID).find('.FLUBcolumn1').css('height',col2Height+'px');
        // Column 2 Rows Resizing
        var col2Height = $('#block' + this.widgetId +blockID).find('.FLUBcolumn2').outerHeight(); //console.log('col2Height = '+col2Height);
        var col2row1Height = col2Height - 23;
        $('#block' + this.widgetId +blockID).find('.FLUBcolumn2row1').css('height',col2row1Height+'px');

    }

    this.refreshFieldsets = function()
    {
        var manager = this;
        $('fieldset').find('div.fileListUploaderBlock' + this.widgetId).each(function(){
            var blockID = this.id; //console.log('blockID = '+blockID);
            var blockID = blockID.split('block'); //console.log('blockID = '+blockID);
            $($(this).find('img')[0]).load(function(){
                manager.initBlock(blockID[1]);
            });
            $(this).find('a.zoombox').zoombox();
        });

        if(this.maxNumberOfFiles > 1) // If multiple files can be added.
        {
            // If we have reached the maximum number of files.
            if($('#blocks'+manager.widgetId).children().length >= this.maxNumberOfFiles)
            {
                //Remove the add button.
                $('#blocks'+manager.widgetId).find('.fa-plus').hide();
            }
            else //Show the add button.
            {
                $('#blocks'+manager.widgetId).find('.fa-plus').show();
            }
        }
    }

    this.hookEvents = function(id)
    {
        var manager = this; //Variable used for event callbacks.

        // Instantiates the class that takes care of the ajax uploading.
        // Merge user defined options with static options.
        this.uploader = new qq.FileUploader($.extend({
            blockId: id,
            element: $('#fileUploader'+this.widgetId+id)[0], // Pass the dom node.
            multiple: false, //Only upload one file at the time.,
            onComplete: function(id, fileName, responseJSON)
            {
                $('#fileName' + manager.widgetId  + this.blockId).html(responseJSON['fileName']);
                var fileName = responseJSON['fileName'];
                var filePath = '".$fileUploaderTempDirectory."/' + fileName;
                var extension = fileName.substr(fileName.lastIndexOf('.') + 1).toLowerCase(); //console.log('extension : '+extension);
                var filePreview = manager.createFilePreview(extension,filePath,id,fileName);
                $('#block' + manager.widgetId + this.blockId).find('.FLUBcolumn1').html(filePreview);
                manager.refreshFieldsets();
                manager.refreshFileList();
                $('#filePath' + manager.widgetId + this.blockId).html(filePath);
            },
            messages: 
            {
                typeError: '".$typeErrorMessage."',
                sizeError: '".$sizeErrorMessage."',
                minSizeError: '".$minSizeErrorMessage."',
                emptyError: '".$emptyErrorMessage."',
                onLeave: '".$onLeaveMessage."'          
            },
            labels:
            {
                upload: '".$uploadMessage."',
                cancel: '".$cancelMessage."',
                failed: '".$failedMessage."'
            }
        }, this.options)); 

        $('#delete' + this.widgetId + id).click(function()
        {
            $('#block'+manager.widgetId+id).remove();
            manager.refreshFileList();
            // If the last block was deleted, create a empty one.
            if($('#blocks' + manager.widgetId).children().length == 0)
            {
                $('#blocks' + manager.widgetId).append(manager.createBlock(manager.maxId, '', '', true));
                manager.hookEvents(manager.maxId);
            }
            manager.refreshFieldsets();
        });

        // Only if multiple files are allowed are those events necessary.
        if(this.maxNumberOfFiles > 1)
        {
            $('#add' + this.widgetId + id).click(function()
            {
                $('#block'+manager.widgetId+id).after(manager.createBlock(manager.maxId, '', '', true));
                manager.hookEvents(manager.maxId);
                manager.refreshFileList();
                manager.refreshFieldsets();
            });

            $('#up' + this.widgetId + id).click(function()
            {
                var prev = $('#blocks'+manager.widgetId).children()[$(this).parents('.block').index() - 1];
                if($.isEmptyObject(prev)){ return; }
                $(prev).before($('#block'+manager.widgetId+id));
                manager.refreshFileList();
                manager.refreshFieldsets();
            });

            $('#down' + this.widgetId + id).click(function()
            {
                var next = $('#blocks'+manager.widgetId).children()[$(this).parents('.block').index() + 1];
                if($.isEmptyObject(next)){ return; }
                $(next).after($('#block'+manager.widgetId+id));
                manager.refreshFileList();
                manager.refreshFieldsets();
            });
        }    

        this.maxId++;
    }
}
", View::POS_BEGIN);
?>
<?php # FILELIST UPLOADER TRACKER
    // See bug #630
    $this->registerJs('var fileListUploaderTracker = [];', View::POS_HEAD);
    
    // If we just need the scripts, skip the rest.
    if($this->context->scriptsOnly) { return; }
    
    // This hidden field will hold the file list.
    echo \yii\helpers\Html::activeHiddenInput($this->context->model, $this->context->attribute, ['id' => 'fileListField'.$this->context->id]);
    
    $id = $this->context->id;
    
    /* When moving filelistuploaders with jquery, the object is reintepreted and a new uploader is generated because the javascript is reinterpreted.
     * To prevent this, we keep track of all uploaders we have already created to avoid duplication.*/
    $js = "if(fileListUploaderTracker['$id']){ return; }";
    $js .= "var flub$id = new FileListUploader('$id', ";
    //Set file uploader options that have to be set at runtime.
    $js .= \yii\helpers\Json::encode([
        'sizeLimit' => $this->context->maxSize > 0 ? $this->context->maxSize : $fileUploaderSizeLimit,
        'minSizeLimit' => $this->context->minSize,
        'action' => Url::to(['/api/fileListUploader.upload']),
        'allowedExtensions' => \yii\helpers\Json::encode($this->context->allowedExtensions)
    ]);
    $js .= ");
    fileListUploaderTracker['$id'] = true;
    flub$id.maxNumberOfFiles = ".$this->context->maxNumberOfFiles.";";
    
    if($this->context->model->hasErrors())
    {
        
    }
    
    $attribute = $this->context->attribute;
    
    if($this->context->model->$attribute == '') // If there are no saved images.
    {
        $js .= "$('#blocks$id').append(flub$id.createBlock(flub$id.maxId, '', '', true));"; // Create an empty file block.
        $js .= "flub$id.hookEvents(flub$id.maxId);";
    }
    else
    {
        /*As decided in #152, lists are now separated with a comma.
         * We thus need to convert from the old format.*/
        $data = str_replace('|', ',', $this->context->model->$attribute);
    
        // For each saved file, create its file block and link the file.
        foreach(explode(',', $data) as $file)
        {
            if(!file_exists($this->context->directory.'/'.$file)) //If the file does not exist, it may still be in the temp directory.
            {
                $url = Url::to(['/user/temp/']).'/'.$file;
            }
            else
            {
                $url = Url::to([$this->context->directoryUrl]).'/'.$file; //Build the url to the file.
            }
    
            $js .= "$('#blocks$id').append(flub$id.createBlock(flub$id.maxId, '$file', '$url'));";
            $js .= "flub$id.hookEvents(flub$id.maxId);";
        }
    }
    
    $js .= "flub$id.refreshFieldsets();";
    
    $this->registerJs($js, View::POS_READY);
?>
<div id="blocks<?php echo $this->context->id; ?>"></div>
