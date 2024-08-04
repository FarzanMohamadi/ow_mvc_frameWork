<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmthememanager
 * @since 1.0
 */

class FRMTHEMEMANAGER_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        if ( OW::getRequest()->isAjax() )
        {
            return;
        }
    }

    public function settings(){

        $lang = OW::getLanguage();
        $this->setPageHeading($lang->text('frmthememanager', 'admin_settings_title'));
        $this->setPageTitle($lang->text('frmthememanager', 'admin_settings_title'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmthememanager')->getStaticCssUrl() . 'frmthememanager.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmthememanager')->getStaticJsUrl() . 'frmthememanager.js');

        $service = FRMTHEMEMANAGER_BOL_Service::getInstance();
        $allThemes = $service->findAllThemes();
        $themeActionController= OW::getRouter()->urlFor('FRMTHEMEMANAGER_CTRL_ThemeActions', 'index');
        $activeTheme = OW::getConfig()->getValue('frmthememanager', 'activeTheme');
        if( isset($activeTheme) && $activeTheme != null){
            $activeThemeToken = $service->getThemeArrayByKey($activeTheme)['csrf_token'];
        }else{
            $activeTheme = false;
            $activeThemeToken = false;
        }
        $allParentTheme = $service->getParentThemes();
        $themeExists = false;
        if ($allParentTheme != null ){
            $themeExists = true;
        }

        $this->assign('createNewTheme',OW::getRouter()->urlForRoute('create_new_theme_route'));
        $this->assign('uploadNewTheme',OW::getRouter()->urlForRoute('upload_theme_route') );
        $this->assign('themeActionController',$themeActionController);
        $this->assign('allThemes', $allThemes );
        $this->assign('themeExists', $themeExists );
        $this->assign('activeTheme', $activeTheme );
        $this->assign('activeThemeToken', $activeThemeToken );
        $this->assign('mainUrl',  OW::getRouter()->urlForRoute('frmthememanager_admin_setting') );
        $this->assign('editUrl',  OW::getRouter()->urlForRoute('create_new_theme_route') );
        $this->assign('colorPicker',OW::getConfig()->getValue('frmthememanager', 'colorPicker'));

    }

    public function createNewTheme( $params = null ){
        $lang = OW::getLanguage();
        $this->setPageHeading($lang->text('frmthememanager', 'create_new_theme'));
        $this->setPageTitle($lang->text('frmthememanager', 'create_new_theme'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
        $this->addComponent('frmThemeManagerThemeFormCMP', new FRMTHEMEMANAGER_CMP_ThemeForm($params));
        $this->assign('parentThemes',sizeof(FRMTHEMEMANAGER_BOL_Service::getInstance()->getParentThemes()) > 0);
    }

    public function uploadTheme(){
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmthememanager')->getStaticCssUrl() . 'frmthememanager.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmthememanager')->getStaticJsUrl() . 'frmthememanager.js');
        $service = FRMTHEMEMANAGER_BOL_Service::getInstance();
        $lang = OW::getLanguage();
        $this->setPageHeading($lang->text('frmthememanager', 'upload_new_theme'));
        $this->setPageTitle($lang->text('frmthememanager', 'upload_new_theme'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');

        $uploadedTheme = new Form('uploadTheme');
        $uploadedTheme->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $fileField = new FileField('themeFile');
        $fileField->setLabel($lang->text('frmthememanager', 'themeFile'));
        $uploadedTheme->addElement($fileField);
        $overwrite = new CheckboxField('overwrite');
        $overwrite->setLabel($lang->text('frmthememanager', 'overWriteAllow'));
        $uploadedTheme->addElement($overwrite);
        $submit = new Submit('uploadTheme');
        $uploadedTheme->addElement($submit);
        if ( OW::getRequest()->isPost() ) {
            if ($uploadedTheme->isValid($_POST)) {
                $formValues = $uploadedTheme->getValues();
                if( !empty($formValues['csrf_hash']) ){
                    $values = $service->extractTheme();
                    $service->saveNewTheme( $values, false, $formValues['overwrite'] );
                    OW::getFeedback()->info($lang->text('frmthememanager', 'save_success'));
                    if(isset($_GET['backUri'])){
                        $service->afterThemeActionRedirect($_GET['backUri']);
                    }
                }
            }
        }
        $this->addForm($uploadedTheme);
        if(isset($_GET['backUri']) && $_GET['backUri'] == 'appearance'){
            $returnUrl = OW::getRouter()->urlForRoute('admin_themes_choose');
        }else{
            $returnUrl = OW::getRouter()->urlForRoute('frmthememanager_admin_setting');
        }
        $this->assign('returnUrl', $returnUrl );
        $this->assign('parentThemes',sizeof($service->getParentThemes()) > 0);
    }
}
