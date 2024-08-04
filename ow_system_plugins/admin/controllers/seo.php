<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controller
 * @since 1.8.4
 */
class ADMIN_CTRL_Seo extends ADMIN_CTRL_Abstract
{
    /**
     * @var BASE_CMP_ContentMenu
     */
    protected $menu;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // activate menu item
//        OW::getDocument()->getMasterPage()->
//                getMenu(OW_Navigation::ADMIN_SETTINGS)->setItemActive('sidebar_menu_item_seo_settings');

        $this->setPageHeading(OW::getLanguage()->text('admin', 'seo_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_edit');

        // register components
        $this->menu = $this->getMenu();
        $this->addComponent('menu', $this->menu);
    }

    /**
     * Index
     */
    public function index()
    {
//        $this->menu->setItemActive('seo_page');

        $event = new BASE_CLASS_EventCollector("base.collect_seo_meta_data");
        OW::getEventManager()->trigger($event);
        $metaList = $event->getData();

        usort($metaList, function( array $item1, array $item2 ){
            return $item1["sectionLabel"] > $item2["sectionLabel"] ? 1 : -1;
        });

        $sections = array();
        $formData = array();

        if( empty($_GET["section"]) ){
            $currentSection = current($metaList)["sectionKey"];
        }
        else
        {
            $currentSection = trim($_GET["section"]);
        }

        foreach ( $metaList as $item ){
            $sections[$item["sectionKey"]] = $item["sectionLabel"];

            if( $item["sectionKey"] == $currentSection ){
                $formData[] = $item;
            }
        }

        $this->assign("sections", $sections);
        $this->assign("currentSection", $currentSection);
        $this->assign("currentUrl", OW::getRouter()->urlForRoute("admin_settings_seo")."?section=#sec#");

        $form = new ADMIN_CLASS_SeoMetaForm($formData);
        $this->addForm($form);
        $this->assign("entities", $form->getEntities());

        if( OW::getRequest()->isPost() ){
            if( $form->processData($_POST) )
            {
                OW::getFeedback()->info(OW::getLanguage()->text('admin', 'settings_submit_success_message'));
                $this->redirect();
            }
            else
            {
                OW::getFeedback()->error($form->getEmptyElementsErrorMessage());
            }
        }
    }

    /**
     * Sitemap
     */
    public function sitemap()
    {
        $service = BOL_SeoService::getInstance();
        $form = new ADMIN_CLASS_SeoSitemapForm();
        $this->addForm($form);

        // validate and save config
        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $entities = $service->getSitemapEntities();
            $formValues = $form->getValues();

            // save entities status
            foreach ( $entities as $entity => $entityData )
            {
                $formValues[$entity]
                    ? $service->enableSitemapEntity($entity)
                    : $service->disableSitemapEntity($entity);
            }

            // save configs
            OW::getConfig()->saveConfig('base', 'seo_sitemap_schedule_update', $formValues['schedule']);
            OW::getConfig()->saveConfig('base', 'seo_sitemap_user_files_indexing', $formValues['userFilesIndexing']);
            $filename= OW_DIR_ROOT . 'robots.txt';
            $contents = OW::getStorage()->fileGetContent($filename);
            $contents = str_replace( 'Disallow:/ow_userfiles/','', $contents);
            if($formValues['userFilesIndexing'] == null){
                $contents = $contents . "\r\n" . 'Disallow:/ow_userfiles/';
            }
            file_put_contents($filename,$contents);


            // reload the current page
            OW::getFeedback()->info(OW::getLanguage()->text('admin', 'seo_sitemap_settings_updated'));
            $this->redirect();
        }

        // assign view variables
        $this->assign('formEntitites', $form->getEntities());
        $this->assign('sitemapUrl', $service->getSitemapUrl());
    }

    /**
     * Social meta
     */
    public function socialMeta()
    {
        $language = OW::getLanguage();

        $form = new Form("imageForm");

        $el = new FileField("image");
        $el->setLabel($language->text("base", "form_social_meta_logo_label"));
        $el->setDescription($language->text("base", "social_meta_logo_desc"));
        $form->addElement($el);

        $submit = new Submit("submit");
        $submit->setValue(OW::getLanguage()->text("admin", "theme_graphics_upload_form_submit_label"));
        $form->addElement($submit);

        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $this->addForm($form);

        $this->assign("logoUrl", BOL_SeoService::getInstance()->getSocialLogoUrl());

        if ( OW::getRequest()->isPost() )
        {
            $result = UTIL_File::checkUploadedFile($_FILES["image"]);

            if ( !$result["result"] )
            {
                OW::getFeedback()->error($result["message"]);
                $this->redirect();
            }

            if ( !UTIL_File::validateImage($_FILES["image"]["name"]) )
            {
                OW::getFeedback()->error($language->text('base', 'not_valid_image'));
                $this->redirect();
            }

            BOL_SeoService::getInstance()->saveSocialLogo($_FILES["image"]["tmp_name"], "meta_social_logo.".UTIL_File::getExtension($_FILES["image"]["name"]));
            OW::getStorage()->removeFile($_FILES["image"]["tmp_name"]);

            OW::getFeedback()->info(OW::getLanguage()->text('admin', 'theme_graphics_upload_form_success_message'));
            $this->redirect();
        }
    }

    /**
     * Get menu
     *
     * @return BASE_CMP_ContentMenu
     */
    protected function getMenu()
    {
        $items = array();

        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('admin', 'seo_page'));
        $item->setIconClass('ow_ic_files ow_dynamic_color_icon');
        $item->setKey('seo_page');
        $item->setUrl(OW::getRouter()->urlForRoute('admin_settings_seo'));
        $item->setOrder(1);
        $items[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('admin', 'seo_sitemap'));
        $item->setIconClass('ow_ic_script ow_dynamic_color_icon');
        $item->setKey('seo_sitemap');
        $item->setUrl(OW::getRouter()->urlForRoute('admin_settings_seo_sitemap'));
        $item->setOrder(2);
        $items[] = $item;

        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('admin', 'seo_social_meta'));
        $item->setIconClass('ow_ic_flag ow_dynamic_color_icon');
        $item->setKey('seo_social_meta');
        $item->setUrl(OW::getRouter()->urlForRoute('admin_settings_seo_social_meta'));
        $item->setOrder(3);
        $items[] = $item;

        return new BASE_CMP_ContentMenu($items);
    }
}

