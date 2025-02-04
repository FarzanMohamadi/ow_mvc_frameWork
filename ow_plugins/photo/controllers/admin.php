<?php
/**
 * 
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.photo.controllers
 * @since 1.0
 */
class PHOTO_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function __construct()
    {
        parent::__construct();
        
        $this->setPageHeading(OW::getLanguage()->text('photo', 'admin_config'));
        
        $general = new BASE_MenuItem();
        $general->setLabel(OW::getLanguage()->text('photo', 'admin_menu_general'));
        $general->setUrl(OW::getRouter()->urlForRoute('photo_admin_config'));
        $general->setKey('general');
        $general->setIconClass('ow_ic_gear_wheel ow_dynamic_color_icon');
        $general->setOrder(0);
        
        $view = new BASE_MenuItem();
        $view->setLabel(OW::getLanguage()->text('photo', 'admin_menu_view'));
        $view->setUrl(OW::getRouter()->urlForRoute('photo_admin_view'));
        $view->setKey('view');
        $view->setIconClass('ow_ic_picture ow_dynamic_color_icon');
        $view->setOrder(1);
        
        $menu = new BASE_CMP_ContentMenu(array($general, $view));
        $this->addComponent('menu', $menu);
    }

    public function index()
    {
        $this->assign('iniValue', PHOTO_BOL_PhotoService::getInstance()->getMaxUploadFileSize(false));

        $configs = OW::getConfig()->getValues('photo');
        $configSaveForm = new PHOTO_ConfigSaveForm();

        $extendedSettings = array();
        $settingNameList = array();
        $event = new BASE_CLASS_EventCollector('photo.collect_extended_settings');
        OW::getEventManager()->trigger($event);

        if ( ($settings = $event->getData()) && is_array($settings) &&
            count(($settings = array_filter($settings, array($this, 'filterValidSection')))) )
        {
            foreach ( $settings as $setting )
            {
                if ( !array_key_exists($setting['section'], $extendedSettings) )
                {
                    $extendedSettings[$setting['section']] = array(
                        'section_lang' => $setting['section_lang'],
                        'icon' => isset($setting['icon']) ? $setting['icon'] : 'ow_ic_gear_wheel',
                        'settings' => array()
                    );
                }

                foreach ( $setting['settings'] as $name => $input )
                {
                    $settingNameList[$name] = $setting['section'];
                    $extendedSettings[$setting['section']]['settings'][$name] = $input;
                    $configSaveForm->addElement($input);
                }
            }
        }

        if ( OW::getRequest()->isPost() && $configSaveForm->isValid($_POST) )
        {
            $values = $configSaveForm->getValues();
            $config = OW::getConfig();

            $config->saveConfig('photo', 'accepted_filesize', $values['acceptedFilesize']);
            $config->saveConfig('photo', 'album_quota', $values['albumQuota']);
            $config->saveConfig('photo', 'user_quota', $values['userQuota']);
            $config->saveConfig('photo', 'download_accept', (bool)$values['downloadAccept']);
            $config->saveConfig('photo', 'store_fullsize', (bool)$values['storeFullsize']);

            $configs['accepted_filesize'] = $values['acceptedFilesize'];
            $configs['album_quota'] = $values['albumQuota'];
            $configs['user_quota'] = $values['userQuota'];
            $configs['download_accept'] = $values['downloadAccept'];
            $configs['store_fullsize'] = $values['storeFullsize'];

            $eventParams = array_intersect_key($values, $settingNameList);
            $event = new OW_Event('photo.save_extended_settings', $eventParams, $eventParams);
            OW::getEventManager()->trigger($event);
            $eventData = $event->getData();

            foreach ( $settingNameList as $name => $section )
            {
                $extendedSettings[$section]['settings'][$name]->setValue($eventData[$name]);
            }
            
            OW::getFeedback()->info(OW::getLanguage()->text('photo', 'settings_updated'));
        }
        
        $configSaveForm->getElement('acceptedFilesize')->setValue($configs['accepted_filesize']);
        $configSaveForm->getElement('albumQuota')->setValue($configs['album_quota']);
        $configSaveForm->getElement('userQuota')->setValue($configs['user_quota']);
        $configSaveForm->getElement('downloadAccept')->setValue($configs['download_accept']);

        $this->assign('extendedSettings', $extendedSettings);
        
        $this->addForm($configSaveForm);
    }

    private function filterValidSection( $section )
    {
        return count(array_diff(array('section', 'section_lang', 'settings'), array_keys($section))) === 0 &&
            is_array($section['settings']) &&
            count(array_filter($section['settings'], array($this, 'filterValidSettings'))) !== 0;
    }

    private function filterValidSettings( $setting )
    {
        return $setting instanceof FormElement;
    }
    
    public function view( array $params = array() )
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('photo')->getStaticCssUrl() . 'admin.css');
        OW::getDocument()->addOnloadScript(';window.photoAdmin.init();');
        $form = new Form('view-mode');
        $form->setAjaxResetOnSuccess(false);
        $form->setAjax(TRUE);
        $form->bindJsFunction('success', 'function(data)
        {
            if ( data && data.result )
            {
                OW.info(data.msg);
            }
        }');
        
        if ( OW::getRequest()->isPost() )
        {
            OW::getConfig()->saveConfig('photo', 'photo_list_view_classic', (bool)$_POST['photo-list-mode']);
            OW::getConfig()->saveConfig('photo', 'photo_view_classic', (bool)$_POST['photo-view-mode']);
            OW::getConfig()->saveConfig('photo', 'list_view_type', $_POST['list-view']);

            exit(json_encode(array('result' => true, 'msg' => OW::getLanguage()->text('photo', 'settings_updated'))));
        }
        
        $photoListMode = new HiddenField('photo-list-mode');
        $photoListMode->setValue(OW::getConfig()->getValue('photo', 'photo_list_view_classic'));
        $form->addElement($photoListMode);
        
        $albumListMode = new HiddenField('album-list-mode');
        $albumListMode->setValue(OW::getConfig()->getValue('photo', 'album_list_view_classic'));
        $form->addElement($albumListMode);
        
        $photoViewMode = new HiddenField('photo-view-mode');
        $photoViewMode->setValue(OW::getConfig()->getValue('photo', 'photo_view_classic'));
        $form->addElement($photoViewMode);

        $photoListView = new RadioField('list-view');
        $photoListView->addOption('albums', OW::getLanguage()->text('photo', 'menu_albums'));
        $photoListView->addOption('photos', OW::getLanguage()->text('photo', 'photo'));
        $listView = OW::getConfig()->getValue('photo', 'list_view_type');
        $photoListView->setValue($listView);
        $form->addElement($photoListView);

        $submit = new Submit('save');
        $submit->addAttribute('class', 'ow_ic_save ow_positive');
        $submit->setValue(OW::getLanguage()->text('photo', 'btn_edit'));
        $form->addElement($submit);
        
        $this->addForm($form);
    }

    public function uninstall()
    {
        if ( isset($_POST['action']) && $_POST['action'] == 'delete_content' )
        {
            OW::getConfig()->saveConfig('photo', 'uninstall_inprogress', 1);
            OW::getEventManager()->trigger(new OW_Event(PHOTO_BOL_PhotoService::EVENT_UNINSTALL_IN_PROGRESS));
            
            PHOTO_BOL_PhotoService::getInstance()->setMaintenanceMode(true);
            
            OW::getFeedback()->info(OW::getLanguage()->text('photo', 'plugin_set_for_uninstall'));
            $this->redirect();
        }
              
        $this->setPageHeading(OW::getLanguage()->text('photo', 'page_title_uninstall'));
        $this->setPageHeadingIconClass('ow_ic_delete');
        
        $this->assign('inprogress', (bool) OW::getConfig()->getValue('photo', 'uninstall_inprogress'));
        
        $js = new UTIL_JsGenerator();
        $js->jQueryEvent('#btn-delete-content', 'click', 'if ( !confirm("'.OW::getLanguage()->text('photo', 'confirm_delete_photos').'") ) return false;');
        
        OW::getDocument()->addOnloadScript($js);
    }
}

class PHOTO_ConfigSaveForm extends Form
{
    public function __construct()
    {
        parent::__construct('configSaveForm');

        $language = OW::getLanguage();

        $acceptedFilesizeField = new TextField('acceptedFilesize');
        $acceptedFilesizeField->setRequired(true);
        $maxSize = PHOTO_BOL_PhotoService::getInstance()->getMaxUploadFileSize(false);
        $last = strtolower($maxSize[strlen($maxSize) - 1]);
        $realSize = (int)$maxSize;

        switch ( $last )
        {
            case 'g': $realSize *= 1024;
        }

        $sValidator = new FloatValidator(0.5, $realSize);
        $sValidator->setErrorMessage($language->text('photo', 'file_size_validation_error'));
        $acceptedFilesizeField->addValidator($sValidator);
        $this->addElement($acceptedFilesizeField->setLabel($language->text('photo', 'accepted_filesize')));

        $albumQuotaField = new TextField('albumQuota');
        $albumQuotaField->setRequired(true);
        $aqValidator = new IntValidator(0, 1000);
        $albumQuotaField->addValidator($aqValidator);
        $this->addElement($albumQuotaField->setLabel($language->text('photo', 'album_quota')));

        $userQuotaField = new TextField('userQuota');
        $userQuotaField->setRequired(true);
        $uqValidator = new IntValidator(0, 10000);
        $userQuotaField->addValidator($uqValidator);
        $this->addElement($userQuotaField->setLabel($language->text('photo', 'user_quota')));
        
        $downloadAccept = new CheckboxField('downloadAccept');
        $downloadAccept->setLabel($language->text('photo', 'download_accept_label'));
        $downloadAccept->setValue(OW::getConfig()->getValue('photo', 'download_accept'));
        $this->addElement($downloadAccept);
        
        $storeFullsizeField = new CheckboxField('storeFullsize');
        $storeFullsizeField->setLabel($language->text('photo', 'store_full_size'));
        $storeFullsizeField->setValue((bool)OW::getConfig()->getValue('photo', 'store_fullsize'));
        $this->addElement($storeFullsizeField);

        $submit = new Submit('save');
        $submit->setValue($language->text('photo', 'btn_edit'));
        $this->addElement($submit);
    }
}
