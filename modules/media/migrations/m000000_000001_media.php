<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

use \yiingine\modules\customFields\models as customFields;
use \yiingine\modules\customFields\models\FormGroup;
use \app\modules\media\models as models;
use \yiingine\models\MenuItem;
use \yiingine\libs\Functions;

/** Represents a database migration of m000000_000001_media.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com> */
class m000000_000001_media extends \yiingine\console\DbMigration
{
    /** Applies the logic to be executed when applying the migration.
     * @return boolean if the migration can be applied. */
    public function up()
    {
        ################################ TABLES ################################
        
        //Create the table that stores media.
        $this->createTable('media', array(
            'id' => 'pk',
            'view' => 'varchar(63) NOT NULL default ""',
            'type' => 'varchar(63) NOT NULL default ""',
            'dt_crtd' => 'datetime NOT NULL',
            'ts_updt' => 'timestamp NOT NULL default CURRENT_TIMESTAMP',
        ));
        
        ########################## VIEWS AND CSS FILES #########################
        
        $assets = $this->module->basePath.'/migrations/_'.get_class($this).'_assets';
        
        $files = array();
        if($this->_hasMediaClass('Index'))
        {
            // Default (view + css)
            $files[$this->module->basePath.'/views/media/index/default.php'] = Yii::getAlias('@app/modules/media/views/media/index/default.example.php');
            $files[$assets.'/index/default.css'] = Yii::getAlias('@app/modules/media/assets/media/index/default.css');
            // Model class
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/index/Index.php'] = Yii::getAlias('@app/modules/media/models/Index.php');
            // Model admin controller
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/index/IndexController.php'] = Yii::getAlias('@app/modules/media/controllers/admin/IndexController.php');
        }
        if($this->_hasMediaClass('Page'))
        {
            // Admin index
            $files[$this->module->basePath.'/views/admin/page/index.php'] = Yii::getAlias('@app/modules/media/views/admin/page/index.example.php');
            // 2 columns (view)
            $files[$this->module->basePath.'/views/media/page/2columns.php'] = Yii::getAlias('@app/modules/media/views/media/page/2columns.example.php');
            // 1 column (view)
            $files[$this->module->basePath.'/views/media/page/1column.php'] = Yii::getAlias('@app/modules/media/views/media/page/1column.example.php');
            // Model class
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/page/Page.php'] = Yii::getAlias('@app/modules/media/models/Page.php');
            // Model admin controller
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/page/PageController.php'] = Yii::getAlias('@app/modules/media/controllers/admin/PageController.php');
        }
        if($this->_hasMediaClass('Image'))
        {
            // Admin index
            $files[$this->module->basePath.'/views/admin/image/index.php'] = Yii::getAlias('@app/modules/media/views/admin/image/index.example.php');
            // Thumbnail (view)
            $files[$this->module->basePath.'/views/media/image/_thumbnail.php'] = Yii::getAlias('@app/modules/media/views/media/image/_thumbnail.example.php');
            // Model class
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/image/Image.php'] = Yii::getAlias('@app/modules/media/models/Image.php');
            // Model admin controller
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/image/ImageController.php'] = Yii::getAlias('@app/modules/media/controllers/admin/ImageController.php');
        }
        if($this->_hasMediaClass('Video'))
        {
            // Admin index
            $files[$this->module->basePath.'/views/admin/video/index.php'] = Yii::getAlias('@app/modules/media/views/admin/video/index.example.php');
            // Thumbnail (view)
            $files[$this->module->basePath.'/views/media/video/_thumbnail.php'] = Yii::getAlias('@app/modules/media/views/media/video/_thumbnail.example.php');
            // Iframe (view)
            $files[$this->module->basePath.'/views/media/video/_iframe.php'] = Yii::getAlias('@app/modules/media/views/media/video/_iframe.example.php');
            // Model class
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/video/Video.php'] = Yii::getAlias('@app/modules/media/models/Video.php');
            // Model admin controller
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/video/VideoController.php'] = Yii::getAlias('@app/modules/media/controllers/admin/VideoController.php');
        }
        if($this->_hasMediaClass('Gallery'))
        {
            // Admin index
            $files[$this->module->basePath.'/views/admin/gallery/index.php'] = Yii::getAlias('@app/modules/media/views/admin/gallery/index.example.php');
            // Thumbnail (view)
            $files[$this->module->basePath.'/views/media/gallery/_thumbnail.php'] = Yii::getAlias('@app/modules/media/views/media/gallery/_thumbnail.example.php');
            // Slider (view)
            $files[$this->module->basePath.'/views/media/gallery/_slider.php'] = Yii::getAlias('@app/modules/media/views/media/gallery/_slider.example.php');
            // Model class
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/gallery/Gallery.php'] = Yii::getAlias('@app/modules/media/models/Gallery.php');
            // Model admin controller
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/gallery/GalleryController.php'] = Yii::getAlias('@app/modules/media/controllers/admin/GalleryController.php');
        }
        if($this->_hasMediaClass('Insert'))
        {
            // Admin index
            $files[$this->module->basePath.'/views/admin/insert/index.php'] = Yii::getAlias('@app/modules/media/views/admin/insert/index.example.php');
            // Thumbnail (view)
            $files[$this->module->basePath.'/views/media/insert/_thumbnail.php'] = Yii::getAlias('@app/modules/media/views/media/insert/_thumbnail.example.php');
            // Model class
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/insert/Insert.php'] = Yii::getAlias('@app/modules/media/models/Insert.php');
            // Model admin controller
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/insert/InsertController.php'] = Yii::getAlias('@app/modules/media/controllers/admin/InsertController.php');
        }
        if($this->_hasMediaClass('Document'))
        {
            // Admin index
            $files[$this->module->basePath.'/views/admin/document/index.php'] = Yii::getAlias('@app/modules/media/views/admin/document/index.example.php');
            // Thumbnail (view)
            $files[$this->module->basePath.'/views/media/document/_thumbnail.php'] = Yii::getAlias('@app/modules/media/views/media/document/_thumbnail.example.php');
            // Model class
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/document/Document.php'] = Yii::getAlias('@app/modules/media/models/Document.php');
            // Model admin controller
            $files[$this->module->basePath.'/migrations/_m000000_000001_media_assets/document/DocumentController.php'] = Yii::getAlias('@app/modules/media/controllers/admin/DocumentController.php');
        }
        
        $this->copy($files);
        
        ############################# FORM GROUPS ##############################
        
        echo "    > creating form groups ...";
        $time = microtime(true);
        
        $customFieldsModule = $this->module->getModule('mediaFields');
        
        FormGroup::$customFieldsModule = $customFieldsModule;
        
        // INDEX
        $fgTextBlock = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Text block', 'fr' => 'Bloc texte'],
            'level' => 1,
            'position' => 1,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => false
        ]);
        $fgIndexContent = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Home page content', 'fr' => 'Contenu de la page accueil'],
            'level' => 1,
            'position' => 2,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => false
        ]);
        $fgBackground = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Background', 'fr' => 'Arrière plan'],
            'level' => 1,
            'position' => 3,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => true
        ]); 

        // INDEX_BLOCK
        $fgBlockContent = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Block content', 'fr' => 'Contenu du bloc'],
            'level' => 1,
            'position' => 1,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => false
        ]); 
        $fgLink = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Link', 'fr' => 'Lien'],
            'level' => 1,
            'position' => 2,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => false
        ]);  
        
        // PAGE
        $fgPageContent = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Page content', 'fr' => 'Contenu de la page'],
            'level' => 1,
            'position' => 1,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => false
        ]);
        $fgRightColumn = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Right column', 'fr' => 'Colonne de droite'],
            'level' => 1,
            'position' => 2,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => false
        ]);
        //$fgBackground
        $fgScript = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Script', 'fr' => 'Script'],
            'level' => 1,
            'position' => 99,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => true
        ]);
                
        // IMAGE
        $fgFile = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'File', 'fr' => 'Fichier'],
            'level' => 1,
            'position' => 1,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => false
        ]);
        $fgText = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Text', 'fr' => 'Texte'],
            'level' => 1,
            'position' => 2,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => false
        ]);
        //$fgLink
        
        // VIDEO
        $fgEmbedding = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Embedding', 'fr' => 'Intégration'],
            'level' => 1,
            'position' => 1,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => false
        ]);
        $fgInformations = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Details', 'fr' => 'Informations'],
            'level' => 1,
            'position' => 2,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => false
        ]);
                
        // GALLERY
        $fgGalleryContent = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Gallery content', 'fr' => 'Contenu de la galerie'],
            'level' => 1,
            'position' => 1,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => false
        ]);
        //$fgText
        
        // MODULE
        $fgModuleDescription = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Description', 'fr' => 'Description'],
            'level' => 1,
            'position' => 1,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => false
        ]);
        
        // NAVIGATION
        $fgNavigation = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Navigation', 'fr' => 'Navigation'],
            'level' => 1,
            'position' => 1,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => true
        ]);
            $fgMenus = $this->addEntry(new FormGroup(), [
                'name' => ['en' => 'Menus', 'fr' => 'Menus'],
                'level' => 1,
                'position' => 1,
                'parent_id' => $fgNavigation->id,
                'owner' => $customFieldsModule->tableName,
                'collapsed' => true
            ]); 
            $fgUrlRewriting = $this->addEntry(new FormGroup(), [
                'name' => ['en' => 'URL Rewriting Rules', 'fr' => 'Règles de réécriture d\URLs'],
                'level' => 1,
                'position' => 2,
                'parent_id' => $fgNavigation->id,
                'owner' => $customFieldsModule->tableName,
                'collapsed' => true
            ]);
        
        $fgMetaData = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Meta data', 'fr' => 'Méta données'],
            'level' => 2,
            'position' => 2,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => true
        ]);
            $fgMetaTags = $this->addEntry(new FormGroup(), [
                'name' => ['en' => 'Meta tags', 'fr' => 'Balises méta'],
                'level' => 1,
                'position' => 1,
                'parent_id' => $fgMetaData->id,
                'owner' => $customFieldsModule->tableName,
                'collapsed' => true
            ]);
            $fgThumbnail = $this->addEntry(new FormGroup(), [
                'name' => ['en' => 'Thumbnail', 'fr' => 'Vignette'],
                'level' => 1,
                'position' => 2,
                'parent_id' => $fgMetaData->id,
                'owner' => $customFieldsModule->tableName,
                'collapsed' => true
            ]);
        
        $fgParameters = $this->addEntry(new FormGroup(), [
            'name' => ['en' => 'Parameters', 'fr' => 'Paramètres'],
            'level' => 2,
            'position' => 99,
            'owner' => $customFieldsModule->tableName,
            'collapsed' => true
        ]);
            $fgActivation = $this->addEntry(new FormGroup(), [
                'name' => ['en' => 'Activation', 'fr' => 'Activation'],
                'level' => 1,
                'position' => 1,
                'parent_id' => $fgParameters->id,
                'owner' => $customFieldsModule->tableName,
                'collapsed' => true
            ]);
            $fgViews = $this->addEntry(new FormGroup(), [
                'name' => ['en' => 'Views', 'fr' => 'Vues'],
                'level' => 1,
                'position' => 2,
                'parent_id' => $fgParameters->id,
                'owner' => $customFieldsModule->tableName,
                'collapsed' => true
            ]);
            $fgPosition = $this->addEntry(new FormGroup(), [
                'name' => ['en' => 'Position', 'fr' => 'Position'],
                'level' => 1,
                'position' => 3,
                'parent_id' => $fgParameters->id,
                'owner' => $customFieldsModule->tableName,
                'collapsed' => true
            ]);
        
        
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        ############################# MEDIA FIELDS #############################
        
        echo "    > creating media fields ...";
        $time = microtime(true);
        
        # ----------------- HTML FIELD CONFIGURATION HELPER --------------------
        
        $HtmlFieldConfig = [];
        $HtmlFieldConfig['paragraphs'] = 
"return [
    'height' => '250px',
    'options' => [
        'theme_advanced_blockformats' => [
            'Paragraph' => 'p'
        ],
        'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,|,sub,sup,|,bullist,numlist,|,indent,outdent',
        'theme_advanced_buttons2' => 'removeformat,cleanup,|,link,unlink,anchor,|,code,|,search,replace,|,fullscreen',
        'theme_advanced_buttons3' => '',
        'theme_advanced_buttons4' => '',
    ]
];";
        
        # ----------------------------- POSITION -------------------------------
        
        $field = $this->addEntry(new \yiingine\modules\media\models\MediaPositionField($customFieldsModule), array(
            'name' => 'position',
            'title' => array('en' => 'Position', 'fr' => 'Position'),
            'description' => array(
                'en' => 'The position of this item relative to its siblings. Click on "Set on last position" to get the value of the last position + 1 and put this item at the end of the list.', 
                'fr' => 'La position de cet item est relative à ses semblables. Cliquez sur "Régler à la dernière position" pour régler la position à la dernière valeur + 1.',
            ),
            'required' => 1,
            'in_forms' => 1,
            'form_group_id' => $fgPosition->id,
            'default' => 1,
            'protected' => 1,
            'configuration' => var_export(array(), true).';',
            'owners' => $this->_filterOwners(array('Video', 'Gallery')),
        ));
        
        # ------------------------------ DESCRIPTION --------------------------------
        
        $field = $this->addEntry(new customFields\TextField($customFieldsModule), array(
            'name' => 'description',
            'title' => array('en' => 'Description', 'fr' => 'Description'),
            'description' => array(
                'en' => 'The description is used for SEO (Search Engine Optimization). If the field is filled, the value will be used in a meta description tag. A description is useful for search engine indexing or sharing on social platforms. This description should contains importants keywords and must not exceed 150 characters.', 
                'fr' => 'La description est utilisée pour optimiser le réfécement ou SEO (Search Engine Optimization). Si le champs est renseigné, sa valeur sera utilisée dans une balise meta description. Celle-ci est utile pour l\'indexage sur les moteurs de recherches ou pour le partage sur les réseaux sociaux. Cette description doit contenir un maximum de mots clés et ne doit pas dépasser 150 charactères.',
            ),
            'required' => 0,
            'translatable' => 1,
            'configuration' => '',
            'size' => 150,
            'in_forms' => 1,
            'form_group_id' => $fgMetaTags->id,
            'protected' => 1,
            'owners' => $this->_filterOwners(array('Index', 'Page', 'Image', 'Video', 'Gallery', 'Insert', 'Document')),
        ));
        
        # ------------------------------ KEYWORDS --------------------------------
            
        $field = $this->addEntry(new customFields\TextField($customFieldsModule), array(
            'name' => 'keywords',
            'title' => array('en' => 'Keywords', 'fr' => 'Mots-clés'),
            'description' => array(
                'en' => 'The keywords are used for SEO (Search Engine Optimization). If the field is filled, the value will be used in a meta keywords tag. Keywords can be used for search engine indexing. The keywords must be separated by commas.', 
                'fr' => 'Les mots-clés sont utilisés pour optimiser le référencement ou SEO (Search Engine Optimization). Si le champs est renseigné, sa valeur sera utlisée dans une balise meta keywords. Celle-ci est parfois utlisée pour l\'indexage sur les moteurs de recherches. Les différents mots-clés doivent être séparé par des virgules.',
            ),
            'required' => 0,
            'translatable' => 1,
            'configuration' => '',
            'size' => 0,
            'in_forms' => 1,
            'form_group_id' => $fgMetaTags->id,
            'protected' => 1,
            'owners' => $this->_filterOwners(array('Index', 'Page', 'Video', 'Insert', 'Image', 'Gallery', 'Document')),
        ));
        
        # ------------------------------ ENABLED --------------------------------
                            
        $field = $this->addEntry(new customFields\BooleanField($customFieldsModule), array(
            'name' => 'enabled',
            'title' => array('en' => 'Enabled', 'fr' => 'Activé'),
            'description' => array(
                'en' => 'Enables this item. A disabled item cannot be viewed.', 
                'fr' => 'Active cet item. Un item désactivé n\'est pas affiché.',
            ),
            'required' => 1,
            'in_forms' => 1,
            'form_group_id' => $fgActivation->id,
            'default' => 1,
            'protected' => 1,
            'owners' => $this->_filterOwners(array('Index', 'Page', 'Image', 'Video', 'Gallery', 'Insert', 'Document')),
        ));
        
        # ----------------------------- THUMBNAIL ------------------------------
        
        $this->addEntry(new customFields\ImageField($customFieldsModule), array(
            'name' => 'thumbnail',
            'title' => array('en' => 'Thumbnail Image file', 'fr' => 'Fichier de l\'image de la vignette'),
            'description' => array('en' => 'Image file displayed in the right column or for the social sharing. The optimal image resolution is 600x400px. If this field is not set, the thumbnail image set for the home page will be used by default.', 'fr' => 'Fichier image affiché dans la colonne de droite ou pour le partage social. La résolution optimale de l\'image est de 600x400px. Si ce champs n\'est pas renseigné, l\'image de la vignette renseignée pour la page d\'acceuil sera utilisé par défault.'),
            'size' => 0,
            'position' => 1,
            'required' => 0,
            'in_forms' => 1,
            'form_group_id' => $fgThumbnail->id,
            'configuration' => var_export(array('maximumNumberOfFiles' => 1), true), // Only one image is permitted.
            'owners' => $this->_filterOwners(array('Index', 'Page', 'Image', 'Video', 'Gallery', 'Insert', 'Document')),
        ));
        
        # ---------------------------- BACKGROUND ------------------------------
        
        $this->addEntry(new customFields\ImageField($customFieldsModule), array(
            'name' => 'background',
            'title' => array('en' => 'Background Image file', 'fr' => 'Fichier de l\'image d\'arrière plan'),
            'description' => array('en' => 'Image file displayed in the page background. The optimal image width is 1920px but it will be automaticaly cropped to optimize the page loading time. If this field is not set, the page background image set for the home page will be used by default.', 'fr' => 'Fichier image affiché en arrière plan de la page. La largeure optimale de l\'image est de 1920px. Celle-ci sera automatiquement tronquée pour optimiser la durée de chargement de la page. Si ce champs n\'est pas renseigné, l\'image d\'arrière plan renseignée pour la page d\'acceuil sera utilisé par défault.'),
            'form_group_id' => $fgBackground->id,
            'size' => 0,
            'position' => 1,
            'required' => 0,
            'in_forms' => 1,
            'configuration' => var_export(array('maximumNumberOfFiles' => 1), true), // Only one image is permitted.
            'owners' => $this->_filterOwners(array('Index', 'Page')),
        ));

        # ------------------------------- PAGE ---------------------------------
        if($this->_hasMediaClass('Page'))
        {
            $this->addEntry(new customFields\VarcharField($customFieldsModule), array(
                'name' => 'module_owner_id',
                'title' => array('en' => 'Owner module ID', 'fr'=> 'ID du module propriétaire'),
                'size' => 63,
                'required' => 0,
                'configuration' => '',
                'default' => '',
                'owners' => 'Page',
                'protected' => 1,
                'in_forms' => 0,
            ));
            
            $this->addEntry(new customFields\TextField($customFieldsModule), array(
                'name' => 'page_title',
                'title' => array('en' => 'Title', 'fr' => 'Titre'),
                'description' => array('en' => 'Title for this page.', 'fr' => 'Titre pour cette page.'),
                'form_group_id' => $fgPageContent->id,
                'position' => 1,
                'required' => 1,
                'in_forms' => 1,
                'owners' => 'Page',
                'translatable' => 1,
                'configuration' => '',
            ));
            $this->addEntry(new \yiingine\modules\media\models\PhpCodeField($customFieldsModule), array(
                'name' => 'before_render',
                'title' => array('en' => 'Before render script', 'fr' => 'Script d\'avant rendu'),
                'description' => array(
                    'en' => 'This script is executed before the rendering of a page to modify is appearance or for defining rendering variables by by returning an array(name => variable).', 
                    'fr' => 'Ce script est exécuté avant le rendu de la page pour modifier son apparence ou pour définir des variables de rendu en retournant un array(nom => variable).'
                ),
                'form_group_id' => $fgScript->id,
                'position' => 3,
                'required' => 0,
                'in_forms' => 1,
                'owners' => 'Page',
                'translatable' => 0,
            ));
            $this->addEntry(new customFields\HtmlField($customFieldsModule), array(
                'name' => 'page_content',
                'title' => array('en' => 'Content', 'fr' => 'Contenu'),
                'description' => array('en' => 'The content of this page. If this page is a module, the special tag {{$module}} can be used within the content as a placeholder for the module\'s content.', 'fr' => 'Le contenu de cette page. Si cette page est un module, la balise {{$module}} peut-être utilisée comme emplacement du contenu du module.'),
                'form_group_id' => $fgPageContent->id,
                'position' => 2,
                'required' => 0,
                'in_forms' => 1,
                'owners' => 'Page',
                'translatable' => 1,
            ));
            
            $queryConditions = 'type=\"Page\"';
            if($this->_hasMediaClass('Image'))  $queryConditions .= ' OR type=\"Image\"';
            if($this->_hasMediaClass('Video'))  $queryConditions .= ' OR type=\"Video\"';
            if($this->_hasMediaClass('Gallery'))  $queryConditions .= ' OR type=\"Gallery\"';
            if($this->_hasMediaClass('Insert'))  $queryConditions .= ' OR type=\"Insert\"';
            if($this->_hasMediaClass('Document'))  $queryConditions .= ' OR type=\"Document\"';
            
            $associatableModelClasses = 'array("adminUrl" => (new \app\modules\media\models\Page())->getAdminUrl(), "model" => (new \app\modules\media\models\Page()))';
            if($this->_hasMediaClass('Image')) $associatableModelClasses .= ', array("adminUrl" => (new \app\modules\media\models\Image())->getAdminUrl(), "model" => (new \app\modules\media\models\Image()))';
            if($this->_hasMediaClass('Video')) $associatableModelClasses .= ', array("adminUrl" => (new \app\modules\media\models\Video())->getAdminUrl(), "model" => (new \app\modules\media\models\Video()))';
            if($this->_hasMediaClass('Gallery')) $associatableModelClasses .= ', array("adminUrl" => (new \app\modules\media\models\Gallery())->getAdminUrl(), "model" => (new \app\modules\media\models\Gallery()))';
            if($this->_hasMediaClass('Insert')) $associatableModelClasses .= ', array("adminUrl" => (new \app\modules\media\models\Insert())->getAdminUrl(), "model" => (new \app\modules\media\models\Insert()))';
            if($this->_hasMediaClass('Document')) $associatableModelClasses .= ', array("adminUrl" => (new \app\modules\media\models\Document())->getAdminUrl(), "model" => (new \app\modules\media\models\Document()))';
            
            $this->addEntry(new customFields\ManyToManyField($customFieldsModule), array(
                'name' => 'associated_media',
                'title' => array('en' => 'Associated objects', 'fr' => 'Objets associés'),
                'description' => array(
                    'en' => 'Images, videos, galleries, inserts, documents or pages to be associated to this object. The right column is displayed only if the view "2 Columns" is chosen for this page.',
                    'fr' => 'Images, vidéos, galeries, encarts, documents ou pages à associer à cet objet. La colonne droite est affichée uniquement si la vue "2 Colonnes" est selectionée pour cette page.'
                ),
                'form_group_id' => $fgRightColumn->id,
                'position' => 2,
                'configuration' => var_export(array(
                    "modelClass" => "\yiingine\modules\media\models\Medium", 
                    "queryConditions" => '"'.$queryConditions.'"',
                    "associatableModelClasses" => "array($associatableModelClasses)"
                ), true),
                'in_forms' => 1,
                'owners' => $this->_filterOwners(array('Index', 'Page')),
            ));
        }
        
        # ------------------------------- INDEX -------------------------------
        if($this->_hasMediaClass('Index'))
        {
            $this->addEntry(new customFields\TextField($customFieldsModule), array(
                'name' => 'index_title',
                'title' => array('en' => 'Home page title', 'fr' => 'Titre de la page d\'accueil'),
                'description' => array('en' => 'Title for the home page.', 'fr' => 'Titre la page d\'accueil.'),
                'form_group_id' => $fgTextBlock->id,
                'position' => 1,
                'required' => 1,
                'in_forms' => 1,
                'owners' => 'Index',
                'translatable' => 1,
                'configuration' => '',
            ));
            $this->addEntry(new customFields\HtmlField($customFieldsModule), array(
                'name' => 'index_content',
                'title' => array('en' => 'Home page text', 'fr' => 'Texte de la page d\'accueil'),
                'description' => array('en' => 'The text of the home page.', 'fr' => 'Le texte de la page d\'accueil.'),
                'form_group_id' => $fgTextBlock->id,
                'position' => 2,
                'required' => 0,
                'in_forms' => 1,
                'owners' => 'Index',
                'translatable' => 1,
                'configuration' => '',
            ));
            $this->addEntry(new customFields\ManyToManyField($customFieldsModule), array(
                'name' => 'associated_index_gallery',
                'title' => array('en' => 'Associated gallery', 'fr' => 'Galerie associée'),
                'description' => array(
                    'en' => 'Galleries to be associated to the home page.',
                    'fr' => 'Galeries à associer à la page d\'acceuil.'
                ),
                'form_group_id' => $fgIndexContent->id,
                'position' => 1,
                'configuration' => var_export(array(
                    "modelClass" => "\yiingine\modules\media\models\Medium", 
                    "queryConditions"  => "type=\"Gallery\"", 
                    "associatableModelClasses" => 'array(array("adminUrl" => (new \app\modules\media\models\Gallery())->getAdminUrl(), "model" => (new \app\modules\media\models\Gallery())))'
                ), true),
                'in_forms' => 1,
                'owners' => 'Index',
            ));
        }
                
        # ------------------------------- IMAGE --------------------------------
        if($this->_hasMediaClass('Image'))
        {
            $this->addEntry(new customFields\ImageField($customFieldsModule), array(
                'name' => 'image_image',
                'title' => array('en' => 'Image file', 'fr' => 'Fichier de l\'image'),
                'description' => array('en' => 'File attached to the image object.', 'fr' => 'Fichier attaché à cet objet image.'),
                'form_group_id' => $fgFile->id,
                'size' => 0,
                'position' => 1,
                'required' => 1,
                'in_forms' => 1,
                'configuration' => var_export(array('maximumNumberOfFiles' => 1), true), //Only one image is permitted.
                'owners' => 'Image',
            ));
            $this->addEntry(new customFields\TextField($customFieldsModule), array(
                'name' => 'image_title',
                'title' => array('en' => 'Title', 'fr' => 'Titre'),
                'description' => array('en' => 'Title for this image.', 'fr' => 'Titre pour cette image.'),
                'form_group_id' => $fgText->id,
                'position' => 1,
                'size' => 255,
                'required' => 0,
                'in_forms' => 1,
                'configuration' => '',
                'owners' => 'Image',
                'translatable' => 1,
            ));
            $this->addEntry(new customFields\HtmlField($customFieldsModule), array(
                'name' => 'image_text',
                'title' => array('en' => 'Texte', 'fr' => 'Texte'),
                'description' => array('en' => 'The html text displayed with this image.', 'fr' => 'Le texte html affiché avec cette image.'),
                'form_group_id' => $fgText->id,
                'position' => 2,
                'required' => 0,
                'in_forms' => 1,
                'configuration' => $HtmlFieldConfig['paragraphs'],
                'owners' => 'Image',
                'translatable' => 1,
            ));
            $this->addEntry(new customFields\TextField($customFieldsModule), array(
                'name' => 'image_link',
                'title' => array('en' => 'Link', 'fr' => 'Lien'),
                'description' => array('en' => 'Link followed when clicking on the image. If this field is not specified, a zoom on the image will be displayed.', 'fr' => 'Lien suivi lorsque l\'on clic sur l\'image. Si ce champs n\'est pas renseigné, un zoom sur l\'image sera affiché.'),
                'form_group_id' => $fgLink->id,
                'position' => 1,
                'size' => 255,
                'required' => 0,
                'in_forms' => 1,
                'configuration' => '',
                'owners' => 'Image',
                'translatable' => 1,
            ));
        }
        
        # ------------------------------- VIDEO --------------------------------
        if($this->_hasMediaClass('Video'))
        {
            $this->addEntry(new customFields\TextField($customFieldsModule), array(
                'name' => 'video_iframe',
                'title' => array('en' => 'Iframe tag', 'fr' => 'Balise iframe'),
                'description' => array('en' => 'HTML code for the embedding provided by the video hosting (Youtube, Dailymotion, Vimeo,...).', 'fr' => 'Code HTML pour l\'intégration fournis par l\'hébergeur de la vidéo (Youtube, Dailymotion, Viméo,...).'),
                'form_group_id' => $fgEmbedding->id,
                'position' => 1,
                'required' => 1,
                'in_forms' => 1,
                'configuration' => '',
                'owners' => 'Video',
            ));
            $this->addEntry(new customFields\TextField($customFieldsModule), array(
                'name' => 'video_title',
                'title' => array('en' => 'Title', 'fr' => 'Titre'),
                'description' => array('en' => 'Title for this video.', 'fr' => 'Titre pour cette vidéo.'),
                'form_group_id' => $fgInformations->id,
                'position' => 1,
                'required' => 0,
                'in_forms' => 1,
                'configuration' => '',
                'owners' => 'Video',
                'translatable' => 1,
            ));
            $this->addEntry(new customFields\HtmlField($customFieldsModule), array(
                'name' => 'video_text',
                'title' => array('en' => 'Text', 'fr' => 'Texte'),
                'description' => array('en' => 'The html text displayed with this video.', 'fr' => 'Le texte html affiché avec cette vidéo.'),
                'form_group_id' => $fgInformations->id,
                'position' => 2,
                'required' => 0,
                'in_forms' => 1,
                'configuration' => $HtmlFieldConfig['paragraphs'],
                'owners' => 'Video',
                'translatable' => 1,
            ));
            $this->addEntry(new customFields\DateField($customFieldsModule), array(
                'name' => 'video_date',
                'title' => array('en' => 'Date', 'fr' => 'Date'),
                'description' => array('en' => 'Date displayed with the video, if specified.', 'fr' => 'Date affichée avec la vidéo, si renseignée.'),
                'form_group_id' => $fgInformations->id,
                'position' => 3,
                'required' => 0,
                'in_forms' => 1,
                'owners' => 'Video',
            ));
            $this->addEntry(new customFields\TextField($customFieldsModule), array(
                'name' => 'video_duration',
                'title' => array('en' => 'Duration', 'fr' => 'Durée'),
                'description' => array('en' => 'The duration of the video.', 'fr' => 'La durée de la vidéo.'),
                'form_group_id' => $fgInformations->id,
                'position' => 4,
                'required' => 0,
                'in_forms' => 1,
                'configuration' => '',
                'owners' => 'Video',
            ));
            $this->addEntry(new customFields\EnumField($customFieldsModule), array(
                'name' => 'video_language',
                'title' => array('en' => 'Language', 'fr' => 'Langue'),
                'description' => array('en' => 'The language of the video.', 'fr' => 'La langue de la vidéo.'),
                'form_group_id' => $fgInformations->id,
                'position' => 5,
                'required' => 0,
                'in_forms' => 1,
                'configuration' => 
"return [
    'data' => [
        'en' => 'English',
        'fr' => 'French',
        'multi' => 'Multilingual'
    ]
];",
                'owners' => 'Video',
            ));
        }
        
        # ------------------------------ GALLERY -------------------------------
        if($this->_hasMediaClass('Gallery'))
        {
            $this->addEntry(new customFields\TextField($customFieldsModule), array(
                'name' => 'gallery_title',
                'title' => array('en' => 'Title', 'fr' => 'Titre'),
                'description' => array('en' => 'Title for this gallery.', 'fr' => 'Titre pour cette galerie.'),
                'form_group_id' => $fgText->id,
                'position' => 1,
                'size' => 255,
                'required' => 0,
                'in_forms' => 1,
                'configuration' => '',
                'owners' => 'Gallery',
                'translatable' => 1,
            ));
            $this->addEntry(new customFields\HtmlField($customFieldsModule), array(
                'name' => 'gallery_text',
                'title' => array('en' => 'Text', 'fr' => 'Texte'),
                'description' => array('en' => 'The html text displayed with this gallery.', 'fr' => 'Le texte html affiché avec cette galerie.'),
                'form_group_id' => $fgText->id,
                'position' => 2,
                'required' => 0,
                'in_forms' => 1,
                'configuration' => $HtmlFieldConfig['paragraphs'],
                'owners' => 'Gallery',
                'translatable' => 1,
            ));
            $this->addEntry(new customFields\ManyToManyField($customFieldsModule), array(
                'name' => 'gallery_items',
                'title' => array('en' => 'Associated items', 'fr' => 'Éléments associés'),
                'description' => array(
                    'en' => 'Images or Videos to be associated to this gallery.',
                    'fr' => 'Images ou Vidéos à associer à cette galerie.'
                ),
                'form_group_id' => $fgGalleryContent->id,
                'position' => 1,
                'configuration' => var_export(array(
                    "modelClass" => "\yiingine\modules\media\models\Medium", 
                    "queryConditions"  => "type=\"Image\"", 
                    "associatableModelClasses" => 'array(array("adminUrl" => (new \app\modules\media\models\Image())->getAdminUrl(), "model" => (new \app\modules\media\models\Image())))'
                ), true),
                'in_forms' => 1,
                'owners' => 'Gallery',
            ));
        }
        
        # ------------------------------ INSERT --------------------------------
        if($this->_hasMediaClass('Insert'))
        {
            $this->addEntry(new customFields\TextField($customFieldsModule), array(
                'name' => 'insert_title',
                'title' => array('en' => 'Title', 'fr' => 'Titre'),
                'description' => array('en' => 'Title for this insert. To not display it, select the "Content only" view.', 'fr' => 'Titre pour cet encart. Pour ne pas l\'afficher, selectionner la vue "Contenu seul".'),
                'form_group_id' => $fgText->id,
                'position' => 1,
                'size' => 255,
                'required' => 0,
                'in_forms' => 1,
                'configuration' => '',
                'owners' => 'Insert',
                'translatable' => 1,
            ));
            $this->addEntry(new customFields\HtmlField($customFieldsModule), array(
                'name' => 'insert_text',
                'title' => array('en' => 'Text', 'fr' => 'Text'),
                'description' => array('en' => 'The html text displayed in this insert.', 'fr' => 'Le texte html affiché dans cet encart.'),
                'form_group_id' => $fgText->id,
                'position' => 2,
                'required' => 1,
                'in_forms' => 1,
                'configuration' => $HtmlFieldConfig['paragraphs'],
                'owners' => 'Insert',
                'translatable' => 1,
            ));
        }
        
        # ----------------------------- DOCUMENT -------------------------------
        if($this->_hasMediaClass('Document'))
        {
            $this->addEntry(new customFields\FileField($customFieldsModule), array(
                'name' => 'document_file',
                'title' => array('en' => 'File', 'fr' => 'Fichier'),
                'description' => array('en' => 'Title for this document. All file formats allowed.', 'fr' => 'Fichier attaché à ce document. Tous les formats de fichiers sont autorisés.'),
                'form_group_id' => $fgFile->id,
                'position' => 1,
                'required' => 1,
                'in_forms' => 1,
                'owners' => 'Document',
                'translatable' => 0,
                'configuration' => 
'[
    "maximumNumberOfFiles" => 1,
]',
            ));
            $this->addEntry(new customFields\TextField($customFieldsModule), array(
                'name' => 'document_title',
                'title' => array('en' => 'Title', 'fr' => 'Titre'),
                'description' => array('en' => 'Title for this document.', 'fr' => 'Titre de ce document.'),
                'form_group_id' => $fgText->id,
                'position' => 1,
                'required' => 0,
                'in_forms' => 1,
                'owners' => 'Document',
                'translatable' => 1,
                'configuration' => '',
            ));
            $this->addEntry(new customFields\HtmlField($customFieldsModule), array(
                'name' => 'document_text',
                'title' => array('en' => 'Text', 'fr' => 'Texte'),
                'description' => array('en' => 'The html text displayed with this document.', 'fr' => 'Le texte html affiché avec ce document.'),
                'form_group_id' => $fgText->id,
                'position' => 2,
                'required' => 0,
                'in_forms' => 1,
                'configuration' => $HtmlFieldConfig['paragraphs'],
                'owners' => 'Document',
                'translatable' => 1,
            ));
        }
        
        # ----------------------- ASSOCIATED MENU ITEMS ------------------------
        $this->addEntry(new \yiingine\modules\media\models\AssociatedMenuItemsField($customFieldsModule), array(
            'name' => 'menu_items',
            'title' => array('en' => 'Associated menu items', 'fr' => 'Items de menu associés'),
            'description' => array(
                'en' => 'The menu items associated to this page. Enabling or disabling this model will also enable or disable its menu items. A menu item is positionned in front of the one selectionned in the position input list.', 
                'fr' => 'Les items de menu associés à cette page. Une modification de l\'activation de ce modèle se répercutera sur ses items de menu. Un item de menu se positionne à l\'avant de celui qui est sélectionné dans le choix de la position.'
            ),
            'form_group_id' => $fgMenus->id,
            'position' => 1,
            'in_forms' => 0, //Handled in special way by the medium form.
            'owners' => 'Page,Module,Index',
        ));
                
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";     
        
        ############################ MEDIA SAMPLES ############################
        
        echo "    > creating sample media ...";
        $time = microtime(true);
        
        # ------------------------------ INDEX -------------------------------    
        
        if($this->_hasMediaClass('Index'))
        {    
            // Index Background
            copy($assets.'/test-image-1.jpg', Yii::getAlias('@webroot/user/temp/_test_background_1.jpg'));
            // Index Thumbnail
            copy($assets.'/test-image-1.jpg', Yii::getAlias('@webroot/user/temp/_test_thumbnail_1.jpg'));
            $model = $this->addEntry(new models\Index(), array(
                'index_title' => array('en' => 'The home page main title must be full of keywords', 'fr' => 'Le titre de la page d\'accueil doit être rempli de mots-clés'),
                'index_content' => array(
                    'en' => self::getLoremIpsum('en',1,0).\yii\helpers\Html::tag('p','<b>John Doe</b><br /> Manager of this application.', array('class'=>'signature')),
                    'fr' => self::getLoremIpsum('fr',1,0).\yii\helpers\Html::tag('p','<b>John Doe</b><br /> Gérant de cette application.', array('class'=>'signature')),
                ),
                'background' => '_test_background_1.jpg',
                'thumbnail' => '_test_thumbnail_1.jpg',
                'description' => array('en' => 'A meta description for the index page containing keywords like keyword 1, keyword 2 or keyword 3.', 'fr' => 'Une description pour la page index contenant quelques mots clés comme mot-clé 1, mot-clé 2 ou mot-clé 3.'),
                'keywords' => array('en' => 'keyword 1, keyword 2, keyword 3', 'fr' => 'mot-clé 1, mot-clé 2, mot-clé 3')
            ));
            
            $index = $model;
            
            $this->addEntry(new \yiingine\models\UrlRewritingRule(), array(
                'pattern' => '',
                'route' => 'media/default/index',
                'defaults' => '["id" => "Index"]',
                'system_generated' => 1
            ));
            
            /*$menuItem = $this->addEntry(new MenuItem(), array(
                'name' => array('en' => 'Home', 'fr' => 'Accueil'),
                'parent_id' => 1,
                'position' => 1,
                'route' => '/media/default/index',
                'parameters' => '/id/Index',
                'model_id' => $index->id,
                'model_class' => models\Index::className()
            ));*/
        }
        // Use a PAGE for the index.
        else if($this->_hasMediaClass('Page'))
        {
            $model = $this->addEntry(new models\Page(), array(
                'page_title' => array('en' => 'The home page main title must be full of keywords', 'fr' => 'Le titre de la page d\'accueil doit être rempli de mots-clés'),
                'page_content' => array('en' => self::getLoremIpsum('en'), 'fr' => self::getLoremIpsum('fr')),
                'description' => array('en' => 'A meta description for the index page', 'fr' => 'Une description pour la page index'),
                'keywords' => array('en' => 'keyword 1, keyword 2, keyword 3', 'fr' => 'mot-clé 1, mot-clé 2, mot-clé 3')
            ));
            
            $this->addEntry(new MenuItem(), array(
                'name' => array('en' => 'Home', 'fr' => 'Accueil'),
                'parent_id' => 1,
                'position' => 1,
                'route' => '/media/default/index',
                'parameters' => '/id/'.$model->id,
                'model_id' => $model->id,
                'model_class' => models\Page::className()
            ));
        }
        
        # ------------------------------- IMAGE --------------------------------
        /* Image 1 (with caption + portrait)
         * Image 2 (without caption + landscape)
         * Image 3 (with caption + landscape + deactivated)
         */
        if($this->_hasMediaClass('Image'))
        {
            $images = [];
            
            copy($assets.'/test-image-1.jpg', Yii::getAlias('@webroot/user/temp/_test-image-1.jpg'));
            $images[] = $this->addEntry(new models\Image(), array(
                'image_title' => array('en' => 'Image 1', 'fr' => 'Image 1'),
                'image_text' => array(
                    'en' => '<p>Image 1 caption : Nunc nunc neque, '.Yii::tA(array('en' => 'keyword 3', 'fr' => 'mot-clé 3')).' lacinia quis ultrices id, mollis ac dolor. Suspendisse pretium tellus. Aenean varius bibendum faucibus. Nam in purus fel, non tempus velit. Cras tellus nisl, luctus non congue condimentum, imperdiet pulvinar arcu.</p>', 
                    'fr' => '<p>Légende de l\'image 1 :</b> Nunc nunc neque, '.Yii::tA(array('en' => 'keyword 3', 'fr' => 'mot-clé 3')).' lacinia quis ultrices id, mollis ac dolor. Suspendisse pretium tellus. Aenean varius bibendum faucibus. Nam in purus fel, non tempus velit. Cras tellus nisl, luctus non congue condimentum, imperdiet pulvinar arcu.</p>'
                ),
                'image_image' => '_test-image-1.jpg',
            ));
            
            copy($assets.'/test-image-2.jpg', Yii::getAlias('@webroot/user/temp/_test-image-2.jpg'));
            $images[] = $this->addEntry(new models\Image(), array(
                'image_title' => array('en' => 'Image 2 without caption', 'fr' => 'Image 2 sans légende'),
                'image_image' => '_test-image-2.jpg',
            ));
            
            copy($assets.'/test-image-3.jpg', Yii::getAlias('@webroot/user/temp/_test-image-3.jpg'));
            $images[] = $this->addEntry(new models\Image(), array(
                'image_text' => array(
                    'en' => '<p>Image 3 with link and without title : Nunc nunc neque, lacinia quis ultrices id, mollis ac dolor. Suspendisse pretium tellus. Aenean varius bibendum faucibus. Nam in purus fel, non tempus velit. Cras tellus nisl, luctus '.Yii::tA(array('en' => 'keyword 2', 'fr' => 'mot-clé 2')).' non congue condimentum, imperdiet pulvinar arcu.</p>',
                    'fr' => '<p>Image 3 avec un lien et sans titre : Nunc nunc neque, lacinia quis ultrices id, mollis ac dolor. Suspendisse pretium tellus. Aenean varius bibendum faucibus. Nam in purus fel, non tempus velit. Cras tellus nisl, luctus '.Yii::tA(array('en' => 'keyword 2', 'fr' => 'mot-clé 2')).' non congue condimentum, imperdiet pulvinar arcu.</p>'
                ),
                'image_image' => '_test-image-3.jpg',
                'image_link' => 'http://sample.com',
            ));
        }
        
        # ------------------------------- VIDEO --------------------------------
        /* Video 1 (Youtube)
         * Video 2 (Dailymotion)
         * Video 3 (Vimeo)
         */
        if($this->_hasMediaClass('Video'))
        {
            $videos = [];
            // Video 1 : Youtube
            copy($assets.'/test-image-1.jpg', Yii::getAlias('@webroot/user/temp/_test-image-1.jpg'));
            $videos[] = $this->addEntry(new models\Video(), array(
                'video_title' => array('en' => 'Video 1', 'fr' => 'Vidéo 1'),
                'video_text' => array(
                    'en' => '<p>Sample for a YouTube video with a thumbnail image and all fields filled :</p>'.self::getLoremIpsum('en', 1, 0), 
                    'fr' => '<p>Exemple d\'une vidéo YouTube avec une image de vignette et tous les champs renseignés :</p>'.self::getLoremIpsum('fr', 1, 0),
                ),
                'video_iframe' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/UNXUJZfDk90" frameborder="0" allowfullscreen></iframe>',
                'video_duration' => '4:06',
                'video_language' => 'en',
                'thumbnail' => '_test-image-1.jpg',
                'position' => 1,
            ));
            // Video 2 : Dailymotion
            $videos[] = $this->addEntry(new models\Video(), array(
                'video_title' => array('en' => 'Video 2', 'fr' => 'Vidéo 2'),
                'video_text' => array(
                    'en' => '<p>Sample for a Dailymotion video without thumbnail image and with all the other fields filled : </p>'.self::getLoremIpsum('en', 1, 0), 
                    'fr' => '<p>Exemple d\'une vidéo Dailymotion sans image de vignette et avec tous autres les champs renseignés : </p>'.self::getLoremIpsum('fr', 1, 0),
                ),
                'video_iframe' => '<iframe frameborder="0" width="480" height="270" src="//www.dailymotion.com/embed/video/x2e6e5l" allowfullscreen></iframe><br /><a href="http://www.dailymotion.com/video/x2e6e5l_honey-bees-feeding-on-flower-nectar_animals" target="_blank">Honey Bees feeding on flower nectar</a> <i>par <a href="http://www.dailymotion.com/tjalex4life" target="_blank">tjalex4life</a></i>',
                'video_duration' => '01:01',
                'video_language' => 'fr',
                'position' => 2,
            ));
            // Video 3 : Vimeo
            $videos[] = $this->addEntry(new models\Video(), array(
                'video_text' => array(
                    'en' => '<p>Sample for a Vimeo video without title, thumbnail image, date, duration and language filled : </p>'.self::getLoremIpsum('en', 1, 0), 
                    'fr' => '<p>Exemple d\'une vidéo Vimeo sans titre, image de vignette, date, durée et langue renseignés : </p>'.self::getLoremIpsum('fr', 1, 0),
                ),
                'video_iframe' => '<iframe src="https://player.vimeo.com/video/62348933" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe> <p><a href="https://vimeo.com/62348933">Nikon D7100 AF Test</a> from <a href="https://vimeo.com/thenikonguy">PVA</a> on <a href="https://vimeo.com">Vimeo</a>.</p>',
                'position' => 3,
            ));  
        }
        
        # ------------------------------ GALLERY -------------------------------
        if($this->_hasMediaClass('Gallery'))
        {
            $galleries = [];
            $imagesCopy = $images;
            // Creating 3 galleries
            for($i=0; $i<=2; $i++)
            {
                if($i<=1)
                {
                    $galleryFields = [
                        'gallery_title' => array('en' => 'Gallery '.($i+1), 'fr' => 'Galerie '.($i+1)),
                        'gallery_text' => array(
                            'en' => '<p><b>Gallery '.($i+1).' text :</b> Nunc nunc neque, lacinia quis ultrices id, mollis ac dolor. '.ucfirst(Yii::tA(array('en' => 'keyword', 'fr' => 'mot-clé'))).'3 pretium tellus. Aenean varius bibendum faucibus. Nam in purus fel, non tempus velit. Cras tellus nisl, luctus non congue condimentum, imperdiet pulvinar arcu.</p>',
                            'fr' => '<p><b>Texte de la galerie '.($i+1).' :</b> Nunc nunc neque, lacinia quis ultrices id, mollis ac dolor. '.ucfirst(Yii::tA(array('en' => 'keyword', 'fr' => 'mot-clé'))).'3 pretium tellus. Aenean varius bibendum faucibus. Nam in purus fel, non tempus velit. Cras tellus nisl, luctus non congue condimentum, imperdiet pulvinar arcu.</p>'
                        )
                    ];
                }
                else
                {
                   $galleryFields = [
                        'gallery_text' => array(
                            'en' => '<p><b>Gallery '.($i+1).' text (without title) :</b> Nunc nunc neque, lacinia quis ultrices id, mollis ac dolor. '.ucfirst(Yii::tA(array('en' => 'keyword', 'fr' => 'mot-clé'))).'3 pretium tellus. Aenean varius bibendum faucibus. Nam in purus fel, non tempus velit. Cras tellus nisl, luctus non congue condimentum, imperdiet pulvinar arcu.</p>',
                            'fr' => '<p><b>Texte de la galerie (sans titre) '.($i+1).' :</b> Nunc nunc neque, lacinia quis ultrices id, mollis ac dolor. '.ucfirst(Yii::tA(array('en' => 'keyword', 'fr' => 'mot-clé'))).'3 pretium tellus. Aenean varius bibendum faucibus. Nam in purus fel, non tempus velit. Cras tellus nisl, luctus non congue condimentum, imperdiet pulvinar arcu.</p>'
                        )
                    ];
                }
                $galleryFields['position'] = ($i+1);
                $galleries[$i] = $this->addEntry(new models\Gallery(), $galleryFields);
                // Gallery image children
                foreach($imagesCopy as $k => $image)
                {
                    $galleries[$i]->link('gallery_items', $image, ['relation_position' => $k + 1]);
                }
                // Each time delete the first image of the children list
                unset($imagesCopy[$i-1]);
                // For the first gallery
                if($i==0)
                {
                    // Gallery video child
                    $galleries[$i]->link('gallery_items', $videos[0], ['relation_position' => count($images)+1 + 1]);
                    if($this->_hasMediaClass('Index'))
                    {
                        // Index gallery
                        $index->link('associated_index_gallery', $galleries[0], ['relation_position' => 1]);
                    }
                }
            }
        }
        
        # ------------------------------ INSERT -------------------------------
        if($this->_hasMediaClass('Insert'))
        {
            $inserts = [];
            // Insert 1
            copy($assets.'/test-image-1.jpg', Yii::getAlias('@webroot/user/temp/_test-image-1_fr.jpg'));
            $imageInTheTextHtmlFr = '<p><img title="Image Test dans un encart" src="{{baseURL}}/user/temp/_test-image-1_fr.jpg" alt="Image Test dans un encart" width="280" height="150" /></p>';
            copy($assets.'/test-image-1.jpg', Yii::getAlias('@webroot/user/temp/_test-image-1_en.jpg'));
            $imageInTheTextHtmlEn = '<p><img title="Test Image in insert" src="{{baseURL}}/user/temp/_test-image-1_en.jpg" alt="Test Image in insert" width="280" height="150" /></p>';
            $inserts[] = $this->addEntry(new models\Insert(), [
                'insert_title' => [
                    'en' => 'Insert 1',
                    'fr' => 'Encart 1',
                ],
                'insert_text' => [
                    'en' => self::getLoremIpsum('en', 1, 0),
                    'fr' => self::getLoremIpsum('fr', 1, 0),
                ],
            ]);
            // Insert 2
            $inserts[] = $this->addEntry(new models\Insert(), array(
                'insert_title' => [
                    'en' => 'Insert 2 with image',
                    'fr' => 'Encart 2 avec image',
                ],
                'insert_text' => array(
                    'en' => $imageInTheTextHtmlEn.self::getLoremIpsum('en', 1, 0),
                    'fr' => $imageInTheTextHtmlFr.self::getLoremIpsum('fr', 1, 0),
                ),
            ));
            // Insert 3
            $inserts[] = $this->addEntry(new models\Insert(), array(
                'insert_text' => array(
                    'en' => '<p>Insert without title : '.strip_tags(self::getLoremIpsum('en', 1, 0)).'</p>',
                    'fr' => '<p>Encart sans titre : '.strip_tags(self::getLoremIpsum('fr', 1, 0)).'</p>'
                ),
            ));
        }
        
        # ------------------------------ DOCUMENT ------------------------------
        if($this->_hasMediaClass('Document'))
        {
            $documents = [];
            // Document 1 (PDF)
            copy($assets.'/document/file.pdf', Yii::getAlias('@webroot/user/temp/_file.pdf'));
            copy($assets.'/test-image-1.jpg', Yii::getAlias('@webroot/user/temp/_test-image-1.jpg'));            
            $documents[] = $this->addEntry(new models\Document(), array(
                'document_file' => '_file.pdf',
                'document_title' => array(
                    'en' => 'Document 1',
                    'fr' => 'Document 1',
                ),
                'document_text' => array(
                    'en' => '<p>Document description : Nunc nunc neque, '.Yii::tA(array('en' => 'keyword 3', 'fr' => 'mot-clé 3')).' lacinia quis ultrices id, mollis ac dolor. Suspendisse pretium tellus. Aenean varius bibendum faucibus. Nam in purus fel, non tempus velit. Cras tellus nisl, luctus non congue condimentum, imperdiet pulvinar arcu.</p>', 
                    'fr' => '<p>Description du document : Nunc nunc neque, '.Yii::tA(array('en' => 'keyword 3', 'fr' => 'mot-clé 3')).' lacinia quis ultrices id, mollis ac dolor. Suspendisse pretium tellus. Aenean varius bibendum faucibus. Nam in purus fel, non tempus velit. Cras tellus nisl, luctus non congue condimentum, imperdiet pulvinar arcu.</p>'
                ),
                'thumbnail' => '_test-image-1.jpg',
            ));
            // Document 2 (ZIP)
            copy($assets.'/document/file.zip', Yii::getAlias('@webroot/user/temp/_file.zip'));
            $documents[] = $this->addEntry(new models\Document(), array(
                'document_file' => '_file.zip',
                'document_title' => array(
                    'en' => 'Document 2 without image',
                    'fr' => 'Document 2 sans image',
                ),
                'document_text' => array(
                    'en' => '<p>Document description : Nunc nunc neque, '.Yii::tA(array('en' => 'keyword 3', 'fr' => 'mot-clé 3')).' lacinia quis ultrices id, mollis ac dolor. Suspendisse pretium tellus. Aenean varius bibendum faucibus. Nam in purus fel, non tempus velit. Cras tellus nisl, luctus non congue condimentum, imperdiet pulvinar arcu.</p>', 
                    'fr' => '<p>Description du document : Nunc nunc neque, '.Yii::tA(array('en' => 'keyword 3', 'fr' => 'mot-clé 3')).' lacinia quis ultrices id, mollis ac dolor. Suspendisse pretium tellus. Aenean varius bibendum faucibus. Nam in purus fel, non tempus velit. Cras tellus nisl, luctus non congue condimentum, imperdiet pulvinar arcu.</p>'
                ),
            ));
        }
        
        # ------------------------------- PAGE ---------------------------------
        if($this->_hasMediaClass('Page'))
        {
            $pages = $subMenus = [];
            
            // FIRST PAGE
            // Images in the text
            copy($assets.'/test-image-1.jpg', Yii::getAlias('@webroot/user/temp/_test-image-1_fr.jpg'));
            copy($assets.'/test-image-2.jpg', Yii::getAlias('@webroot/user/temp/_test-image-2_fr.jpg'));
            copy($assets.'/test-image-3.jpg', Yii::getAlias('@webroot/user/temp/_test-image-3_fr.jpg'));
            $imagesInTextFr = '<p><img src="{{baseURL}}/user/temp/_test-image-1_fr.jpg" alt="Image test dans le texte 1" width="1920" height="750" /></p>';
            $imagesInTextFr .= '<div class="row">';
            $imagesInTextFr .= '<div class="col-sm-6">';
            $imagesInTextFr .= '<p><img src="{{baseURL}}/user/temp/_test-image-2_fr.jpg" alt="Image test dans le texte 2" width="1920" height="750" /></p>';
            $imagesInTextFr .= '</div>';
            $imagesInTextFr .= '<div class="col-sm-6">';
            $imagesInTextFr .= '<p><img src="{{baseURL}}/user/temp/_test-image-3_fr.jpg" alt="Image test dans le texte 3" width="1920" height="750" /></p>';
            $imagesInTextFr .= '</div>';
            $imagesInTextFr .= '</div>';
            $imagesInTextFr .= '<div class="row">';
            $imagesInTextFr .= '<div class="col-sm-4">';
            $imagesInTextFr .= '<p><img src="{{baseURL}}/user/temp/_test-image-1_fr.jpg" alt="Image test dans le texte 1" width="1920" height="750" /></p>';
            $imagesInTextFr .= '</div>';
            $imagesInTextFr .= '<div class="col-sm-4">';
            $imagesInTextFr .= '<p><img src="{{baseURL}}/user/temp/_test-image-2_fr.jpg" alt="Image test dans le texte 2" width="1920" height="750" /></p>';
            $imagesInTextFr .= '</div>';
            $imagesInTextFr .= '<div class="col-sm-4">';
            $imagesInTextFr .= '<p><img src="{{baseURL}}/user/temp/_test-image-3_fr.jpg" alt="Image test dans le texte 3" width="1920" height="750" /></p>';
            $imagesInTextFr .= '</div>';
            $imagesInTextFr .= '</div>';
            copy($assets.'/test-image-1.jpg', Yii::getAlias('@webroot/user/temp/_test-image-1_en.jpg'));
            copy($assets.'/test-image-2.jpg', Yii::getAlias('@webroot/user/temp/_test-image-2_en.jpg'));
            copy($assets.'/test-image-3.jpg', Yii::getAlias('@webroot/user/temp/_test-image-3_en.jpg'));
            $imagesInTextEn = '<p><img src="{{baseURL}}/user/temp/_test-image-1_en.jpg" alt="Image in the text 1" width="1920" height="750" /></p>';
            $imagesInTextEn .= '<div class="row">';
            $imagesInTextEn .= '<div class="col-sm-6">';
            $imagesInTextEn .= '<p><img src="{{baseURL}}/user/temp/_test-image-2_en.jpg" alt="Image in the text 2" width="1920" height="750" /></p>';
            $imagesInTextEn .= '</div>';
            $imagesInTextEn .= '<div class="col-sm-6">';
            $imagesInTextEn .= '<p><img src="{{baseURL}}/user/temp/_test-image-3_en.jpg" alt="Image in the text 3" width="1920" height="750" /></p>';
            $imagesInTextEn .= '</div>';
            $imagesInTextEn .= '</div>';
            $imagesInTextEn .= '<div class="row">';
            $imagesInTextEn .= '<div class="col-sm-4">';
            $imagesInTextEn .= '<p><img src="{{baseURL}}/user/temp/_test-image-1_en.jpg" alt="Image test dans le texte 1" width="1920" height="750" /></p>';
            $imagesInTextEn .= '</div>';
            $imagesInTextEn .= '<div class="col-sm-4">';
            $imagesInTextEn .= '<p><img src="{{baseURL}}/user/temp/_test-image-2_en.jpg" alt="Image test dans le texte 2" width="1920" height="750" /></p>';
            $imagesInTextEn .= '</div>';
            $imagesInTextEn .= '<div class="col-sm-4">';
            $imagesInTextEn .= '<p><img src="{{baseURL}}/user/temp/_test-image-3_en.jpg" alt="Image test dans le texte 3" width="1920" height="750" /></p>';
            $imagesInTextEn .= '</div>';
            $imagesInTextEn .= '</div>';
            // Page Background
            copy($assets.'/test-image-1.jpg', Yii::getAlias('@webroot/user/temp/_test_background_1.jpg'));
            // Page Thumbnail
            copy($assets.'/test-image-1.jpg', Yii::getAlias('@webroot/user/temp/_test_thumbnail_1.jpg'));
            // Creating Page
            $pages[] = $model = array($this->addEntry(new models\Page(), array(
                'page_title' => array('en' => 'First Page', 'fr' => 'Première Page'),
                'page_content' => array(
                    'en' => self::getLoremIpsum('en', 2, 2).$imagesInTextEn.self::getLoremIpsum('en', 1, 2),
                    'fr' => self::getLoremIpsum('fr', 2, 2).$imagesInTextFr.self::getLoremIpsum('fr', 1, 2)),
                'background' => '_test_background_1.jpg',
                'thumbnail' => '_test_thumbnail_1.jpg',
                'description' => array(
                    'en' => 'A meta description for the first page : Aliquam erat volutpat. Praesent varius vehicula felis. Pellentesque dictum mauris vitae dictum.',
                    'fr' => 'Une description pour la première page : Aliquam erat volutpat. Praesent varius vehicula felis. Pellentesque dictum mauris vitae dictum.'),
                'keywords' => array('en' => 'keyword 1', 'fr' => 'mot-clé 1'),
            )), 1, 2, array('en' => 'Page 1', 'fr' => 'Page 1'));
            // Image 1 associated
            if($this->_hasMediaClass('Image'))
            {
                $model[0]->link('associated_media', $images[0], ['relation_position' => 1]);
            }
            // Video 1 associated
            if($this->_hasMediaClass('Video'))
            {
                $model[0]->link('associated_media', $videos[0], ['relation_position' => 2]);
            }
            // Insert 1 associated
            if($this->_hasMediaClass('Insert'))
            {
                $model[0]->link('associated_media', $inserts[0], ['relation_position' => 3]);
            }
            // Document 1 associated
            if($this->_hasMediaClass('Document'))
            {
                $model[0]->link('associated_media', $documents[0], ['relation_position' => 4]);
            }
            // Gallery 1 associated
            if($this->_hasMediaClass('Gallery'))
            {
                $model[0]->link('associated_media', $galleries[0], ['relation_position' => 5]);
            }
            
            // SECOND PAGE
            // Gallery in the text
            $imageGalleryInTheTextHtmlFr = '<div class="corpusGallery"><span class="corpusGalleryMarker">{{gallery}}</span>';
            $imageGalleryInTheTextHtmlEn = '<div class="corpusGallery"><span class="corpusGalleryMarker">{{gallery}}</span>';
            for($i=1;$i<=3;$i++)
            {
                copy($assets.'/test-image-'.$i.'.jpg', Yii::getAlias('@webroot/user/temp/_test-image-'.$i.'_fr.jpg'));
                $imageGalleryInTheTextHtmlFr .= '<img title="Image '.$i.' de Galerie Test dans le texte" src="{{baseURL}}/user/temp/_test-image-'.$i.'_fr.jpg" alt="Image '.$i.' de Galerie Test dans le texte" width="1920" height="1440" />';
                copy($assets.'/test-image-'.$i.'.jpg', Yii::getAlias('@webroot/user/temp/_test-image-'.$i.'_en.jpg'));
                $imageGalleryInTheTextHtmlEn .= '<img title="Image '.$i.' of Test Gallery in the text" src="{{baseURL}}/user/temp/_test-image-'.$i.'_en.jpg" alt="Image '.$i.' of Test Gallery in the text" width="1920" height="1440" />';
            }
            $imageGalleryInTheTextHtmlFr .= '<span class="corpusGalleryMarker">{{/gallery}}</span></div>';
            $imageGalleryInTheTextHtmlEn .= '<span class="corpusGalleryMarker">{{/gallery}}</span></div>';
            // Page Background
            copy($assets.'/test-image-2.jpg', Yii::getAlias('@webroot/user/temp/_test_background_2.jpg'));
            // Page Thumbnail
            copy($assets.'/test-image-2.jpg', Yii::getAlias('@webroot/user/temp/_test_thumbnail_2.jpg'));
            // Creating Page
            $pages[] = $model = array($this->addEntry(new models\Page(), array(
                'page_title' => array('en' => 'Second Page', 'fr' => 'Seconde Page'),
                'page_content' => array(
                    'en' => self::getLoremIpsum('en', 2, 2).$imageGalleryInTheTextHtmlEn.self::getLoremIpsum('en', 1, 2),
                    'fr' => self::getLoremIpsum('fr', 2, 2).$imageGalleryInTheTextHtmlFr.self::getLoremIpsum('fr', 1, 2)),
                'background' => '_test_background_2.jpg',
                'thumbnail' => '_test_thumbnail_2.jpg',
                'description' => array(
                    'en' => 'A meta description for the second page : Aliquam erat volutpat. Praesent varius vehicula felis. Pellentesque dictum mauris vitae dictum.',
                    'fr' => 'Une description pour la seconde page : Aliquam erat volutpat. Praesent varius vehicula felis. Pellentesque dictum mauris vitae dictum.'),
                'keywords' => array('en' => 'keyword 2', 'fr' => 'mot-clé 2'),
            )), 1, 3, array('en' => 'Page 2', 'fr' => 'Page 2'));
            // Image 2 associated
            if($this->_hasMediaClass('Image'))
            {
                $model[0]->link('associated_media', $images[1], ['relation_position' => 1]);
            }
            // Video 2 associated
            if($this->_hasMediaClass('Video'))
            {
                $model[0]->link('associated_media', $videos[1], ['relation_position' => 2]);
            }
            // Insert 2 associated
            if($this->_hasMediaClass('Insert'))
            {
                $model[0]->link('associated_media', $inserts[1], ['relation_position' => 3]);
            }
            // Document 2 associated
            if($this->_hasMediaClass('Document'))
            {
                $model[0]->link('associated_media', $documents[1], ['relation_position' => 4]);
            }
            // Gallery 2 associated
            if($this->_hasMediaClass('Gallery'))
            {
                $model[0]->link('associated_media', $galleries[1], ['relation_position' => 5]);
            }
            // Page 1 associated
            $model[0]->link('associated_media', $pages[0][0], ['relation_position' => 6]);
            
            // THIRD PAGE
            $pages[] = $model = array($this->addEntry(new models\Page(), array(
                'page_title' => array('en' => 'About', 'fr' => 'À propos'),
                'page_content' => array(
                    'en' => self::getLoremIpsum('en', 1, 1).'{{$test}}'.self::getLoremIpsum('en', 2, 2), 
                    'fr' => self::getLoremIpsum('fr', 1, 1).'{{$test}}'.self::getLoremIpsum('fr', 2, 2),
                ),
                'keywords' => array('en' => 'keyword 3', 'fr' => 'mot-clé 3'),
                'before_render' => '$test = "<div class=\"jumbotron\"><p>".Yii::tA(["en" => "Before render test succefully passed !", "fr" => "Test du before render réussit !"])."</p></div>";'."\n".'return [\'test\' => $test];',
            )), 1, 4, array('en' => 'About', 'fr' => 'À propos'));
            // Image 3 associated
            if($this->_hasMediaClass('Image'))
            {
                $model[0]->link('associated_media', $images[2], ['relation_position' => 1]);
            }
            // Video 3 associated
            if($this->_hasMediaClass('Video'))
            {
                $model[0]->link('associated_media', $videos[2], ['relation_position' => 2]);
            }
            // Insert 3 associated
            if($this->_hasMediaClass('Insert'))
            {
                $model[0]->link('associated_media', $inserts[2], ['relation_position' => 3]);
            }
            // Gallery 3 associated
            if($this->_hasMediaClass('Gallery'))
            {
                $model[0]->link('associated_media', $galleries[2], ['relation_position' => 5]);
            }
            // Page 2 associated
            $model[0]->link('associated_media', $pages[1][0], ['relation_position' => 6]);
            
            // LEGAL NOTICE / MENTIONS LEGALES
            $pages[] = $subMenus[] = array($this->addEntry(new models\Page(), array(
                'page_title' => array('en' => '<h1>Legal Notice</h1>', 'fr' => '<h1>Mentions Légales</h1>'),
                'page_content' => array(
                    'en' => 
'<h2>Owner</h2>
<ul>
<li>Name : NAME</li>
<li>Last Name : LAST_NAME</li>
<li>Company : COMPANY NAME</li>
<li>Address : ADDRESS</li>
</ul>
<h2>Webmaster</h2>
<ul>
<li>Creation, Development and Maintenance : <a href="http://example.com" title="EXAMPLE | Interactive and Inovative Communication">EXAMPLE</a></li>
</ul>
</p>',
                    'fr' => 
'<h2>Propriétaire</h2>
<ul>
<li>Nom : NOM</li>
<li>Prénom : PRÉNOM</li>
<li>Entreprise : NOM DE L\'ENTREPRISE</li>
<li>Addresse : ADDRESSE</li>
</ul>
<h2>Webmestre</h2>
<ul>
<li>Création, Développement et Maintenance : <a href="http://www.example.com" title="EXAMPLE | Communication Intéractive et Innovante">EXAMPLE</a></li>
</ul>
</p>'
                )
            )), 2, 1, array('en' => 'Legal Notice', 'fr' => 'Mentions Légales'), array('en' => 'Legal Notice', 'fr' => 'Mentions Légales'));
            
            // GENERAL TERMS OF SALE
            $pages[] = $subMenus[] = $model = array($this->addEntry(new models\Page(), array(
                'page_title' => array('en' => '<h1>General terms of sale</h1>', 'fr' => '<h1>Conditions générales de vente</h1>'),
                'page_content' => array(
                    'en' => self::getLoremIpsum('en', 5, 3),
                    'fr' => self::getLoremIpsum('fr', 5, 3)
                ),
                'view' => '/media/page/1column'
            )), 2, 2, array('en' => 'General terms of sale', 'fr' => 'Conditions générales de vente'), array('en' => 'General terms of sale', 'fr' => 'Conditions générales de vente'));
            
            // ALL GALLERIES
            if($this->_hasMediaClass('Gallery'))
            {
                $pages[] = $subMenus[] = $model = array($this->addEntry(new models\Page(), array(
                    'page_title' => array('en' => '<h1>All galleries</h1>', 'fr' => '<h1>Toutes les galleries</h1>'),
                    'page_content' => array(
                        'en' => '<p>{{$galleryList}}</p>'.self::getLoremIpsum('en', 1, 1),
                        'fr' => '<p>{{$galleryList}}</p>'.self::getLoremIpsum('fr', 1, 1)
                    ),
                    'view' => '/media/page/1column',
                    'before_render' => '

return array(
  \'galleryList\' => \\yiingine\\modules\\media\\components\\widgets\\PagedList::widget([\'model\' => $model, \'query\' => \\app\\modules\\media\\models\\Gallery::findEnabled()])
);',
                )), 2, 3, array('en' => 'Galleries', 'fr' => 'Galeries'), array('en' => 'Galleries', 'fr' => 'Galleries'));
            }
            
            // ALL VIDEOS
            if($this->_hasMediaClass('Video'))
            {
                $pages[] = $model = array($this->addEntry(new models\Page(), array(
                    'page_title' => array('en' => '<h1>All videos</h1>', 'fr' => '<h1>Toutes les vidéos</h1>'),
                    'page_content' => array(
                        'en' => '<p>{{$videoList}}</p>'.self::getLoremIpsum('en', 1, 1),
                        'fr' => '<p>{{$videoList}}</p>'.self::getLoremIpsum('fr', 1, 1)
                    ),
                    'view' => '/media/page/1column',
                    'before_render' => '
return [
  \'videoList\' => \\yiingine\\modules\\media\\components\\widgets\\PagedList::widget([\'model\' => $model, \'query\' => \\app\\modules\\media\\models\\Video::findEnabled()])
];',
                )), 2, 4, array('en' => 'Videos', 'fr' => 'Vidéos'));
            }
            
            // MENU ITEMS
            foreach($pages as $i => $page) // For each page + 1 copied.
            {
                // Create menu items
                $menuItem = $this->addEntry(new MenuItem(), array(
                    'name' => $page[3],
                    'parent_id' => $page[1],
                    'position' => $page[2],
                    'route' => '/media/default/index',
                    'parameters' => '/id/'.$page[0]->id,
                    'model_id' => $page[0]->id,
                    'model_class' => models\Page::className()    
                ));
                
                // URL rewriting rules for each page.
                $currentLanguage = Yii::$app->language; // Save the current language.
                
                foreach(Yii::$app->params['app.available_languages'] as $lang)
                {
                    Yii::$app->language = $lang;
                    $this->addEntry(new \yiingine\models\UrlRewritingRule(), array(
                        'pattern' => Functions::encodeFileName($page[0]->getTitle()),
                        'languages' => $lang,
                        'route' => 'media/default/index',
                        'defaults' => '["id"=>'.$page[0]->id.']',
                        'system_generated' => 1
                    ));
                }
                
                Yii::$app->language = $currentLanguage; // Restore the current language.
            }
            
            // Create a menu with submenus.
            
            $menuWithSubMenus = $this->addEntry(new MenuItem(), array(
                'name' => ['fr' => 'Info', 'en' => 'Info'],
                'parent_id' => 1,
                'position' => 5,
                'route' => '',
            ));
            
                foreach($subMenus as $j => $subMenu) // For each subMenus (menu 1, 2 and 3)
                {
                    // Create sub menu items
                    $this->addEntry(new MenuItem(), array(
                        'name' => $subMenu[4],
                        'parent_id' => $menuWithSubMenus->id,
                        'route' => '/media/default/index',
                        'parameters' => '/id/'.$subMenu[0]->id,
                        'model_id' => $subMenu[0]->id,
                        'model_class' => models\Page::className()
                    ));
                }
            
            // Get last position in footerMenu
            $lastPosition = MenuItem::find()->select('max(position) as max')->where(['parent_id' => 2])->scalar();
            // Create a second menu item in footerMenu for the first page to test double menu locking.
            $menuItem = $this->addEntry(new MenuItem(), array(
                'name' => array('en' => 'Page 1', 'fr' => 'Page 1'),
                'parent_id' => 2,
                'position' => $lastPosition + 1,
                'route' => '/media/default/index',
                'parameters' => '/id/'.$pages[0][0]->id,
                'model_id' => $pages[0][0]->id,
                'model_class' => models\Page::className()
            ));
        }
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
        
        # associated_media -----------------------------------------------------
        if($this->_hasMediaClass('Index'))
        {
            $i = 0;
            // Page 1 associated
            if($this->_hasMediaClass('Page'))
            {
                $i++;
                $index->link('associated_media', $pages[0][0], ['relation_position' => $i]);
            }
            // Image 1 associated
            if($this->_hasMediaClass('Image'))
            {
                $i++;
                $index->link('associated_media', $images[0], ['relation_position' => $i]);
            }
            // Video 1 associated
            if($this->_hasMediaClass('Video'))
            {
                $index->link('associated_media', $videos[0], ['relation_position' => $i]);
            }
            // Insert 1 associated
            if($this->_hasMediaClass('Insert'))
            {
                $index->link('associated_media', $inserts[0], ['relation_position' => $i]);
            }
            // Document 1 associated
            if($this->_hasMediaClass('Document'))
            {
                $index->link('associated_media', $documents[0], ['relation_position' => $i]);
            }
            // Gallery 1 associated
            if($this->_hasMediaClass('Gallery'))
            {
                $index->link('associated_media', $galleries[0], ['relation_position' => $i]);
            }
        }
        
        ####################### MENU ITEMS #######################
        
        echo "    > creating media module admin menus ...";
        $time = microtime(true);
        
        // Find the modules admin menu.
        if($adminMenu = MenuItem::find()->where(array('name' => 'adminMenu'))->one())
        {        
            //$route = ($this->_hasMediaClass('Index')) ? '/media/admin/index/index' : '/media/admin/page/index' ;
            $contentMenu = $this->addEntry(new MenuItem(), array(
                'name' => array('en' => 'Content', 'fr' => 'Contenu'), 
                'parent_id' => $adminMenu->id, 
                'side' => MenuItem::ADMIN,
                'route' => '/media/admin/page/index', // $route,
                'position' => 2,
                'rule' => 'Yii::$app->getModule("media")->checkAccess()'
            ));
            
            // Add a menu entry for each media type.
            $position = 1;
            foreach($this->module->mediaClasses as $class)
            {
                $route = '/media/admin/'.lcfirst($class::shortClassName()).'/index';
                $rule = '(Yii::$app->user->can("Medium-view") || Yii::$app->user->can("Medium-'.$class::shortClassName().'-view"))';
                
                $name = [];
                $currentLanguage = Yii::$app->language;
                foreach(Yii::$app->getParameter('app.supported_languages') as $language)
                {
                    Yii::$app->language = $language;
                    $name[$language] = $class::getModelLabel();
                }
                Yii::$app->language = $currentLanguage;
                
                $this->addEntry(new MenuItem(), array(
                    'name' => $name, 
                    'parent_id' => $contentMenu->id, 
                    'side' => MenuItem::ADMIN,
                    'route' => $route,
                    'position' => $position++,
                ));
            }
            
            echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
            
            //Create the menu items for the custom fields module.
            (new \yiingine\modules\customFields\migrations\CustomFieldsAdminMenuItems($contentMenu, $this->module->getModule('mediaFields'), array('fr' => 'Media', 'en' => 'Media')))->up();
        }
        
        ####################### PERMISSIONS #######################
        
        echo "    > creating permissions ...";
        $time = microtime(true);
        
        $models = array();
        
        foreach($this->module->mediaClasses as $class)
        {
            $models[] = 'Medium-'.$class::shortClassName();
        }
        
        $models[] = 'Module-Page';
        
        $this->createModelPermissions($this->module->id, $models);
        
        echo " done (time: ".sprintf('%.3f', microtime(true)-$time)."s)\n";
    }
    
    /** Applies the logic to be executed when removing a migration.
     * @return boolean if the migration can be removed.*/
    public function down()
    {
        echo "m000000_000001_media does not support migration down.\n";
        return false;
    }
    
    /**
     * Checks within the module's configuration if it has a certain media class.
     * @param string $class the name of the class.
     * @return boolean if $class is present.
     * */
    private function _hasMediaClass($class)
    {
        foreach($this->module->mediaClasses as $mediaClass)
        {
            if(strpos($mediaClass, $class) !== false)
            {
                return true;
            }
        }
        
        return false;
    }
    
    /** Remove those owner that are not set.
     * @param array $owners the tentative owners list.
     * @return string the filtered list of owners.*/
    private function _filterOwners($owners)
    {
        foreach($owners as $i => $class)
        {
            if(!$this->_hasMediaClass($class)) // If this class does not exist.
            {
                unset($owners[$i]);
            }
        }
        
        return implode(',', $owners);
    }
}
