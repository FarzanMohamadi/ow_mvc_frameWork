<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmthememanager
 * @since 1.0
 */
class FRMTHEMEMANAGER_CMP_ThemeForm extends OW_Component
{
    /**
     * Constructor.
     */
    public function __construct( $params = null)
    {
        parent::__construct();

        if( isset($params) && $params != null ){
            $key =  $params['key'];
        }

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmthememanager')->getStaticCssUrl() . 'frmthememanager.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmthememanager')->getStaticJsUrl() . 'frmthememanager.js');
        $themeActionController= OW::getRouter()->urlFor('FRMTHEMEMANAGER_CTRL_ThemeActions', 'index');
        $service = FRMTHEMEMANAGER_BOL_Service::getInstance();
        $themeObject = array();
        $edit = false;
        if( isset($key) && $key != null ){
            $themeObject = $service->getThemeArrayByKey( $key );
            $edit = true;
        }

        $lang = OW::getLanguage();

        $NewTheme = new Form('newTheme');
        $NewTheme->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $name = new TextField('themeName');
        $name->setLabel($lang->text('frmthememanager','theme_name'));
        $name->setRequired(true);
        if($edit){
            $name->setValue($themeObject['themeName']);
        }
        $NewTheme->addElement($name);

        $key = new TextField('themeKey');
        $key->setLabel($lang->text('frmthememanager','theme_key'));
        $key->addValidator(new StringValidator());                                               //todo: validator must validate the key to assure that it is english
        $key->setRequired(true);
        if($edit){
            $key->setValue($themeObject['themeKey']);
        }
        $NewTheme->addElement($key);

        $parentTheme = new Selectbox('parentTheme');
        $options = array();
        $allParentTheme = $service->getParentThemes();
        foreach ($allParentTheme as $parentThemeItem){
            $options[$parentThemeItem->key] = $parentThemeItem->title;
        }
        if($edit){
            $parentTheme->setValue($themeObject['parentTheme']);
        }
        $parentTheme->setOptions($options);
        $parentTheme->setHasInvitation(true);
        $parentTheme->setRequired(true);
        $parentTheme->setLabel($lang->text('frmthememanager','parent_theme'));
        $NewTheme->addElement($parentTheme);

        foreach ($service->colorsList as $colorName){
            $color = new TextField($colorName);                                              //todo: default values must be fetched from parent theme
            $color->setLabel($lang->text('frmthememanager',$colorName));
            if($edit){
                $color->setValue($themeObject['themeColors'][$colorName]);
            }
            $NewTheme->addElement($color);
        }
        $renderUrls = $service->urlsList;
        foreach ($service->urlsList as $key=> $urlName){
            $fileField = new FileField($urlName['name']);
            $fileField->setLabel($lang->text('frmthememanager', $urlName['name']));
            if($edit){
                $urls = $themeObject['urls'];
                if(isset($urls[$urlName['name']])){
                    $renderUrls[$key] = array_merge($renderUrls[$key],array("url"=>$urls[$urlName['name']]));
                }
            }
            $NewTheme->addElement($fileField);
        }

        $fileRemoveList = new HiddenField( 'fileRemoveList' );
        $fileRemoveList->addAttribute("id", "file_remove_list");
        $NewTheme->addElement($fileRemoveList);


        $categoryField = new CheckboxGroup('themeConfigs');
        $configs = $service->configList;
        foreach ($configs as $config ){
            $categoryField->addOption( $config['fileName'] , $lang->text('frmthememanager',$config['name']));
        }
        if($edit){
            $categoryField->setValue($themeObject['configs']);
        }
        $categoryField->setLabel(OW::getLanguage()->text('frmthememanager', 'theme_configs'));
        $NewTheme->addElement($categoryField);


        $style = new Textarea('themeStyle');
        $style->setLabel(OW::getLanguage()->text('frmthememanager', 'customize_css'));
        if($edit){
            $style->setValue($themeObject['themeStyle']);
        }
        $NewTheme->addElement($style);

        $footerTags = new Textarea('footerTags');
        $footerTags->setLabel(OW::getLanguage()->text('frmthememanager', 'footer_tags_label'));
        if($edit){
            $footerTags->setValue($themeObject['footerTags']);
        }
        $NewTheme->addElement($footerTags);

        $submit = new Submit('createTheme');
        if($edit){
            $submit->setValue(OW::getLanguage()->text('admin', 'save_btn_label'));
        }else{
            $submit->setValue(OW::getLanguage()->text('frmthememanager', 'verify_creation'));
        }
        $NewTheme->addElement($submit);

        if ( OW::getRequest()->isPost() ) {
            if ($NewTheme->isValid($_POST)) {
                $values = $NewTheme->getValues();
                if(!empty($values['themeName']) && !empty($values['themeKey'])){
                    $values = $service->processFormValues($values);
                    $service->saveNewTheme( $values, $edit, false );
                }
                OW::getFeedback()->info($lang->text('frmthememanager', 'save_success'));
                if(isset($_GET['backUri'])){
                    $service->afterThemeActionRedirect($_GET['backUri']);
                }
            }
        }
        if(isset($_GET['backUri']) && $_GET['backUri'] == 'appearance'){
            $returnUrl = OW::getRouter()->urlForRoute('admin_themes_choose');
        }else{
            $returnUrl = OW::getRouter()->urlForRoute('frmthememanager_admin_setting');
        }
        $this->addForm($NewTheme);
        $this->assign('returnUrl', $returnUrl );
        $this->assign('colorPicker',OW::getConfig()->getValue('frmthememanager', 'colorPicker'));
        $this->assign('themeActionController',$themeActionController);
        $this->assign('colorList', $service->colorsList);
        $this->assign('urlsList', $renderUrls );
        $this->assign('guideLink', OW::getPluginManager()->getPlugin('frmthememanager')->getStaticUrl().'img/Plugin_Guide.png' );
    }
}