<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\modules\customFields\managers;

use \Yii;
 
/** Manages a CustomField of type Html.*/
class Html extends Text
{            
    /**@var array a list of allowed extensions, leave empty for no restiction.*/
    public $extensions = [];
    
    /**
     * @var callback a callback function($manager) to generate the directory where the files will be saved. 
     * */
    public $directory;
    
    /**@var array the default configuration array for tinyMce.*/
    public $defaultConfiguration = [
        'width' => '100%',                                 // The editor width
        'height' => '450px',                                // The editor height
        // Linked CSS
        'editorTemplate' => 'full',                         // Enable all plugins
        'useSwitch' => false,                               // ???
        'useCompression' => false,                          // Otherwise script combination will not work.
        'relative_urls' => false,                           // Required to correctly parse for images.
        'remove_script_host' => false,                      // Required to correctly parse for images.
        'options' => [
            'theme'=>'advanced',                                // Set Advanced Theme
            'theme_advanced_toolbar_location'=> 'top',          // The toolbar location
            'theme_advanced_toolbar_align'=> 'center',          // The toolbar alignment
            'theme_advanced_buttons1' => 'formatselect,|,bold,italic,underline,strikethrough,|,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,indent,outdent',
            'theme_advanced_buttons2' => 'tablecontrols,|,uploadImage,image,media,createGallery',
            'theme_advanced_buttons3' => 'undo,redo,|,removeformat,cleanup,|,pastetext,pasteword,|,link,unlink,anchor,|,code,|,search,replace,|,fullscreen',
            'theme_advanced_buttons4' => '',

            'theme_advanced_path_location' => 'bottom',         // The path location ex : "p > span"
            'theme_advanced_resizing' => false,                 // Disabled the resizing of the editor
            'fullscreen_new_window' => false,                   // Permit to set the size of the fullscreen window
            'fullscreen_settings' => [                     // Fullscreen plugin settings
                'width' => '638px',                             // Fullscreen width (requested size + 18px padding and borders)
                'theme_advanced_resizing' => true,  
            ],
            'force_br_newlines' => false,                       // On press enter will not print a <br />
            'force_p_newlines' => true,                         // On press enter will print a <p></p>
            'forced_root_block' => 'p',                         // Start the editor with <p></p>
            // Paste
            'paste_auto_cleanup_on_paste' => true,              // Clean the code on paste
            //'paste_remove_styles' => true,
            //'paste_remove_styles_if_webkit' => true,
            'paste_strip_class_attributes' => true,
            //'paste_retain_style_properties' => 'none',
            //'paste_text_sticky' => true,
            'paste_text_sticky_default' => true,                // Active the plain text paste by default (pastetext)
            'entity_encoding' => 'raw',                         // All characters will be stored in non-entity form except these XML default entities:
                                                                // &amp; &lt; &gt; &quot;              
            'relative_urls' => false,                           // Disabled relative url from document_base_url
            'setup' => 'tinyMCESetup',                          // JS function to execute on initialization
        ]
    ];

    /** 
     * @inheritdoc
     * */
    protected function renderInputInternal()
    {
        $bundle = \yiingine\assets\admin\AdminAsset::register(Yii::$app->view);
        
        # TINYMCE EDITOR DEFAULT CONFIGURATION
    
        $config = $this->defaultConfiguration;
        
        //Add parameters that cannot be added as attributes.
        
        $config['contentCSS'] = Yii::$app->request->baseUrl.'/site/corpus,'.Yii::$app->request->baseUrl.'/css/views/layouts/font-face.css';
        
        // Format allowed in formatselect
        $config['options']['theme_advanced_blockformats'] = [
            Yii::t(__CLASS__, 'Subtitle {level}', ['level' => 1]) => 'h2',
            Yii::t(__CLASS__, 'Subtitle {level}', ['level' => 2]) => 'h3',
            Yii::t(__CLASS__, 'Subtitle {level}', ['level' => 3]) => 'h4',
            Yii::t(__CLASS__, 'Paragraph') => 'p'
        ];
        
        /* Registers a callback for tinyMCE that adds an custom buttons to
         * the toolbar.
         * uploadImage: This button will be used as a trigger to the valumns-file-uploader
         * script.
         * */
        
         Yii::$app->view->registerJs(
          'function addCustomButtons(ed)'.
          '{'.
            
              // Add uploadImage button.
              'ed.addButton("uploadImage",'.
              '{'.
                  'title: "'.Yii::t(__CLASS__, 'Upload an image').'",'.
                  'image: "'.$bundle->baseUrl.'/images/uploadImage.png'.'",'.
                  'onclick: function()'.
                  '{'.
                      '$(".dummyElement").find("input").click();'. //Triggers a click to launch the file browser dialog.
                  '},'.
              '});'.
            
              // Introduce a delay to let the DOM load properly.
              'setTimeout("createTinyMCEUploader();", 1000);'.
            
              // Add create gallery button.
              'ed.addButton("createGallery",'.
              '{'.
                  'title: "'.Yii::t(__CLASS__, 'Create a gallery').'",'.
                  'image: "'.$bundle->baseUrl.'/images/createGallery.png'.'",'.
                  'onclick: function()'.
                  '{'.
                      'ed.focus();'.
                      // If the selection is not part of a gallery.
                      'if($(ed.selection.getNode()).attr("class") != "gallery" && $(ed.selection.getNode()).parents(".gallery").length == 0)'.
                      '{'.
                          // Wraps the selected content in a div and add markers to inform the user those images are part of a gallery.
                          'ed.selection.setContent("<div class=\"corpusGallery\"><span class=\"corpusGalleryMarker\">{{gallery}}</span>" + ed.selection.getContent() + "<span class=\"corpusGalleryMarker\">{{/gallery}}</span></div>");'.
                      '}'.
                      'else'.
                      '{'.    
                          // Remove the gallery nodes.
                          'var gallery = $(ed.selection.getNode()).attr("class") == "gallery" ? ed.selection.getNode(): $(ed.selection.getNode()).parents(".gallery");'.
                          '$(gallery).find(".corpusGalleryMarker").remove();'.
                          '$(gallery).before($(gallery).contents());'.
                          '$(gallery).remove();'.
                      '}'.
                  '},'.
              '});'.
            
              'ed.onMouseUp.add(function(ed)' .
              '{' .
                  // If the current selection is part of a gallery, hightlight the createGallery button.
                  'ed.focus();' .
                  'ed.controlManager.setActive("createGallery", $(ed.selection.getNode()).attr("class") == "gallery" || $(ed.selection.getNode()).parents(".gallery").length > 0);' .
              '});' .
              //Sets inputsChanged to true so the user gets prompted if he quits a page without saving his changes.
              //Sets lastChange to prevent session timeout while a user is using the widget.
              'ed.onKeyPress.add(function(ed)' .
              '{'.
                  'if(ed.isDirty)
                  { 
                     inputsChanged = true;'.
                     // Also update the lastEvent time to warn user of session timeouts.
                     (Yii::$app->user->authTimeout !== null ? 'lastChange = new Date().getTime();' : ''). 
                  '}' .
              '});'.
          '}',
        \yii\web\View::POS_BEGIN);
        
        // Registers a callback for tinyMCE that adds a characters counter.
         Yii::$app->view->registerJs(
         'function getCharCount(ed)'.
          '{'.
              // manually setting our max character limit
              'tinymax = ed.settings.char_counter;'.
              // grabbing the length of the curent editors content
              'var body = ed.getBody(), text = tinymce.trim(body.innerText || body.textContent);'.
              'tinylen = text.length;'.
              // setting up the text string that will display in the path area
              'htmlcount = "(" + tinylen + " / " + tinymax + ")" ;'.
              // if the user has exceeded the max turn the path bar red.
              'if (tinylen>tinymax){'.
                  'htmlcount = "<span style=\' color: #f00; !important!\'>" + htmlcount + "</span>";'.
              '}'.
              // this line writes the html count into the path row of the active editor
              'tinymce.DOM.setHTML(tinymce.DOM.get(tinyMCE.activeEditor.id + \'_path_row\'), htmlcount + " ");'.  
          '}'.
          'function initCharCounter(ed)'.
          '{'.
              // Initialization
              'ed.onInit.add(function(ed){ getCharCount(ed); });'.
              // Perform this action every time a key is pressed
              'ed.onKeyUp.add(function(ed, e){ getCharCount(ed); });'.
          '}', 
        \yii\web\View::POS_BEGIN);
        
         Yii::$app->view->registerJs(
          'function tinyMCESetup(ed)
          {
              addCustomButtons(ed);
              if(ed.settings.char_counter) initCharCounter(ed);
          }', 
        \yii\web\View::POS_BEGIN);
        
        // Publish assets and registers script for the file uploader.
        $assetUrl = Yii::$app->assetManager->publish(Yii::getAlias('@yiingine/vendor/valums-file-uploader'))[1];
        Yii::$app->view->registerCssFile($assetUrl.'/fileuploader.css', ['media' => 'screen, projection']);
        Yii::$app->view->registerJsFile($assetUrl.'/fileuploader.js');  
        
        // Instantiate a dummy Element to attach the file uploader to.
        Yii::$app->view->registerJs('$("<div class=\"dummyElement\" style=\"visibility:hidden;position:absolute;\"></div>").appendTo($("body"));' ,\yii\web\View::POS_READY);
        
        /* Register a function to create the file uploader. Upon completion of the upload
         * the url of the file us handed off to the mceAdvImage for foratting and 
         * insertion within the content.*/
        Yii::$app->view->registerJs(
            'function createTinyMCEUploader()' ."\n".
            '{'."\n".
                'var uploader = new qq.FileUploader({'."\n".
                    'element: $(".dummyElement")[0],'."\n". // Pass the dom node.
                    'multiple: false,'."\n". // Only upload one file at the time.
                    'action: "'.\yii\helpers\Url::to(['/api/fileListUploader.upload']).'",'."\n". // Path to server side upload script.
                    'allowedExtensions: ["gif", "jpeg", "jpg", "png"],'."\n".
                    'sizeLimit: '.\yiingine\libs\Functions::returnBytes(ini_get('post_max_size')).','."\n".
                    'onSubmit: function(id, fileName){$(".screenLoaderElement").fadeIn(300);},'."\n".
                    'onComplete: function(id, fileName, responseJSON)'."\n".
                    '{'."\n".
                        'tinyMCE.activeEditor.execCommand("mceAdvImage", false);' . // Instantiate a mceAdvImage popup window.
                        // Put the file path in the src dialog of the second iframe (the first one it the tinyMCE editor itself).
                        'setTimeout(function(){' . //Give the dialog enough time to load before giving it the url.
                            'var url = "'.$bundle->baseUrl.'/user/temp/" + responseJSON["fileName"];' . // Create the url for the image.
                            '$("iframe[id^=mce_]").contents().find("#src").attr("value", url);' . // Put the image url in the form input.
                            '$("iframe[id^=mce_]")[0].contentWindow.ImageDialog.showPreviewImage(url);' . // Shows a preview of the image.
                            '$(".screenLoaderElement").fadeOut(300);' .
                        '}, 1000);' .
                    '},'."\n".
                    'messages: '."\n".
                    '{'."\n".
                        'typeError: "'.Yii::t(__CLASS__, '{file} has invalid extension. Only {extensions} are allowed.').'",'."\n".
                        'sizeError: "'.Yii::t(__CLASS__, '{file} is too large, maximum file size is {sizeLimit}.').'",'."\n".
                        'minSizeError: "'.Yii::t(__CLASS__, '{file} is too small, minimum file size is {minSizeLimit}.').'",'."\n".
                        'emptyError: "'.Yii::t(__CLASS__, '{file} is empty, please select files again without it.').'",'."\n".
                        'onLeave: "'.Yii::t(__CLASS__, 'One or more files are being uploaded, if you leave now the upload will be cancelled.').'"'."\n".        
                    '},'."\n".
                    'labels:'."\n".
                    '{'."\n".
                        'upload: "'.Yii::t(__CLASS__, 'Upload a file').'",'."\n".
                        'cancel: "'.Yii::t(__CLASS__, 'Cancel').'",'."\n".
                        'failed: "'.Yii::t(__CLASS__, 'Failed').'"'."\n".
                    '}'."\n".
                '});'."\n".
            '}',
         \yii\web\View::POS_BEGIN);
        
        // Override the default field configuration with the one given by the field.
        if($this->field->getConfigurationArray())
        {
            $config = array_replace_recursive($config, $this->field->getConfigurationArray());
        }
        
        //Add a special description to this field on top of the user defined one.
        $description = '<ul style="border-top:1px dotted gray;margin:3px 0 0 0;padding:3px 0 0 13px;text-align:left;">';
        // Characters counter
        if(isset($config['options']['char_counter']) && !empty($config['options']['char_counter']))
        {
            $description .= '<li>'.Yii::t(__CLASS__, 'The size of the text should not exceed {charCounter} characters.', array('{charCounter}' => $config['options']['char_counter'])).'</li>';
        }
        // Newlines rules
        if($config['options']['force_p_newlines'])
        {
            $description .= '<li>'.Yii::t(__CLASS__, 'To create a new paragraph press "Enter".').'</li>';
            $description .= '<li>'.Yii::t(__CLASS__, 'To skip a line without changing paragraph press "Shift + Enter".').'</li>';
        }
        else 
        {
            $description .= '<li>'.Yii::t(__CLASS__, 'To create a new paragraph press "Shift + Enter".').'</li>';
            $description .= '<li>'.Yii::t(__CLASS__, 'To skip a line without changing paragraph press "Enter".').'</li>';
        }
        // Clean format
        $description .= '<li>'.Yii::t(__CLASS__, 'If you copy and paste some text, it is advised to clean the format and the code by using the tools "Remove Format" (eraser) and "Clean Code" (brush) to start on a clean base.').'</li>';
        // Render partial inserts explanation
        if(preg_match_all('/\{\{([a-zA-Z0-9 \.]+)\}\}/i', $this->owner->{$this->getAttribute()}, $result) === 1)
        {
            $description .= '<li>'.Yii::t(__CLASS__, 'The text between double braces allows to insert dynamic content, such as a list of objects. It is advised not to edit those items. ex : "{{$listOfObjects}}".').'</li>';
        }
        // Call your webmaster
        $description .= '<li>'.Yii::t(__CLASS__, 'In case of problems please do not hesitate to contact your webmaster.').'</li>';
        $description .= '</ul>';
        
        return array_merge($config, [
            'type' => '\moonland\tinymce\TinyMCE',
            'hint' => $this->field->description.$description
        ]);
    }
    
    /**
     * @inheritdoc
     * */
    public function events()
    {
        return [
            \yii\db\ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            \yii\db\ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            \yii\db\ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            \yii\db\ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            \yiingine\db\ActiveRecord::EVENT_AFTER_CLONE => 'afterClone'
        ];
    }
    
    /** Since the URL of the images need to include the base URL, we run into problems when the
     * content is accessed using different base URLs. To alleviate this, {{baseURL}} is replaced
     * with the actual URL after loading.
     *  @param $event Event the event parameters.*/
    public function afterFind($event)
    {
        if(CONSOLE) //Do not execute this event in console mode.
        {
            return;
        }
        
        // Replace {{baseURL}} with the application's base url.
        $this->owner->{$this->getAttribute()} = str_replace('{{baseURL}}', Yii::$app->request->hostInfo.Yii::$app->request->baseUrl, $this->owner->{$this->getAttribute()});
    }
    
    /** Save files that have been recently added or deleted.
     *  @param $event Event the event parameters.*/
    public function afterSave($event)
    {
        $fileUploader = new \yiingine\widgets\admin\FileListUploader();
        $fileUploader->directory = call_user_func($this->directory, $this);
        $fileUploader->maxNumberOfFiles = PHP_INT_MAX;
        $fileUploader->allowedExtensions = $this->extensions;
        $fileUploader->init();
    
        $images = $this->_extractFiles();
        $newImages = $images[0];
        $existingImages = $images[1];
        
        $fileUploader->fileList = array_merge($newImages, $existingImages);
    
        $fileUploader->save();
        
        $string = 'src="'.(CONSOLE ? '{{baseURL}}' :Yii::$app->request->hostInfo.Yii::$app->request->baseUrl);
        $directory = str_replace(Yii::getAlias('@webroot'), '', call_user_func($this->directory, $this));
        
        foreach(array_diff($fileUploader->fileList, $existingImages) as $i => $image)
        {
            // Replace the path to the new image.
            $this->owner->{$this->getAttribute()} = str_replace($string.'/user/temp/'.$newImages[$i], $string.$directory.'/'.$image, $this->owner->{$this->getAttribute()});
        }
        
        if(!CONSOLE) //Not executed in CONSOLE mode.
        {
            /*Since the URL of the images need to include the base URL, we run into problems when the
             * content is accessed using different base URLs. To alleviate this, the base URL is replaced
             * with {{baseURL}} before saving. */
            $this->owner->{$this->getAttribute()} = str_replace(Yii::$app->request->hostInfo.Yii::$app->request->baseUrl, '{{baseURL}}', $this->owner->{$this->getAttribute()});
        }
    
        // Clean the files that were deleted.
        $fileUploader->clean();
    }
    
    /** Delete files along with the model that owned them.
     *  @param $event Event the event parameters.*/
    public function beforeDelete($event)
    {
        //Delete the files uploaded with FileUploader.
        $fileUploader = new \yiingine\widgets\admin\FileListUploader();
        $fileUploader->fileList = $this->_extractFiles()[1];
        $fileUploader->directory = call_user_func($this->directory, $this);
        $fileUploader->init();
        $fileUploader->purge();
    }
    
    /** 
     * Triggered when a customizable model is cloned.
     * @param Event $event the cloning event. $event->owner is the clone.
     * */
    public function afterClone($event)
    {
        $directory = str_replace(Yii::getAlias('@webroot'), '', call_user_func($this->directory, $this));
        $attribute = $this->getAttribute();
        $tempDir = Yii::getAlias('@webroot/user/temp');
        $content = $event->sender->$attribute;
    
        $copyToTemp = function(&$content, $directory, $tempDir) // Define a function to reuse the copying code.
        {
            $start = $end = 0;
            while(false !== ($start = mb_strpos($content, $directory, $end))) // While the pattern can be found within the content;
            {
                $start += strlen($directory) + 1;
                $end = mb_strpos($content, '"', $start); // Get to the end of the src attribute;
                $file = mb_substr($content, $start, $end - $start);
                $newFile = uniqid().'-'.mb_substr($file, 3);
                copy(Yii::getAlias('@webroot').$directory.'/'.$file, $tempDir.'/'.$newFile);
                $content = str_replace($directory.'/'.$file, '/user/temp/'.$newFile, $content);
            }
        };
    
        $copyToTemp($content, $directory, $tempDir);
    
        $event->sender->$attribute = $content;
    }
    
      /* @return [newFiles, existingFiles] the files defined within this HTML field.*/
    private function _extractFiles()
    {
        $existingImages = []; // Existing images found within the text.
        $newImages = []; // Images that have been uploaded.
        
        // Build the path were saved images go.
        $directory = str_replace(Yii::getAlias('@webroot'), '', call_user_func($this->directory, $this));
        $directory = str_replace('/user/', '', $directory);
        
        $currentPosition = 0; // The current position we are at within the html.
        $start = 0; //The start of the image name.
        $end = 0; // The end of the image name.
        $string = 'src="'.(CONSOLE ? '{{baseURL}}' :Yii::$app->request->hostInfo.Yii::$app->request->baseUrl);
        
        while(false !== ($start = mb_stripos($this->owner->{$this->getAttribute()}, $string, $end)))
        {
            $start += strlen($string);
            //If "temp/" follows immediatly.
            if(mb_strpos($this->owner->{$this->getAttribute()}, '/user/temp/', $start) === $start)
            {
                //This means the image is in the temp folder and is a new addition.
                $start += strlen('/user/temp/'); //Get to the start of the file name.
                $end = mb_strpos($this->owner->{$this->getAttribute()}, '"', $start); //Find the end of the file name.
                //This image's url will have to be replaced.
                $newImages[] = mb_substr($this->owner->{$this->getAttribute()}, $start, $end - $start);
            }
            else //If it is an existing file.
            {
                $start += strlen($directory.'/'); //Get to the start of the file name.
                $end = mb_strpos($this->owner->{$this->getAttribute()}, '"', $start); //Find the end of the file name.
                $existingImages[] = mb_substr($this->owner->{$this->getAttribute()}, $start, $end - $start);
            }
        }
        
        return [array_unique($newImages), array_unique($existingImages)];
    }
}
