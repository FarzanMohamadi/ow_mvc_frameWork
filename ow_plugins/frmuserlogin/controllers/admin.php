<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmuserlogin.controllers
 * @since 1.0
 */
class FRMUSERLOGIN_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index( array $params = array() )
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmuserlogin', 'admin_page_heading'));
        $this->setPageTitle($language->text('frmuserlogin', 'admin_page_title'));
        $currentSectionFromParams = 1;
        if(isset($params['currentSection'])){
            $currentSectionFromParams = $params['currentSection'];
        }
        $sectionsInformation = FRMUSERLOGIN_BOL_Service::getInstance()->getSections($currentSectionFromParams);
        $sections = $sectionsInformation['sections'];
        $currentSection = $sectionsInformation['currentSection'];
        $this->assign('sections',$sections);
        $this->assign('currentSection',$currentSection);
        $config = OW::getConfig();
        $configs = $config->getValues('frmuserlogin');
        if($currentSection==1) {
            $form = new Form('settings');
            $form->setAjax();
            $form->setAjaxResetOnSuccess(false);
            $form->setAction(OW::getRouter()->urlForRoute('frmuserlogin.admin'));
            $form->bindJsFunction(
                Form::BIND_SUCCESS,
                'function(data){if(data.result){OW.info("'.OW::getLanguage()->text(
                    'frmuserlogin',
                    'setting_saved'
                ).'");}else{OW.error("Parser error");}}'
            );


            $numberOfLastLoginDetails = new TextField('numberOfLastLoginDetails');
            $numberOfLastLoginDetails->setLabel($language->text('frmuserlogin', 'numberOfLastLoginDetails'));
            $numberOfLastLoginDetails->setRequired();
            $numberOfLastLoginDetails->addValidator(new IntValidator(1));
            $numberOfLastLoginDetails->setValue($configs['numberOfLastLoginDetails']);
            $form->addElement($numberOfLastLoginDetails);

            $expiredTimeOfLoginDetails = new TextField('expiredTimeOfLoginDetails');
            $expiredTimeOfLoginDetails->setLabel($language->text('frmuserlogin', 'expiredTimeOfLoginDetails'));
            $expiredTimeOfLoginDetails->setRequired();
            $expiredTimeOfLoginDetails->addValidator(new IntValidator(1));
            $expiredTimeOfLoginDetails->setValue($configs['expiredTimeOfLoginDetails']);
            $form->addElement($expiredTimeOfLoginDetails);

            $enableActiveDevices = new CheckboxField('enableActiveDevices');
            $enableActiveDevices->setLabel($language->text('frmuserlogin', 'enableActiveDevices'));
            $enableActiveDevices->setValue($configs['update_active_details']);
            $form->addElement($enableActiveDevices);

            $submit = new Submit('save');
            $form->addElement($submit);

            $this->addForm($form);

            if (OW::getRequest()->isAjax()) {
                if ($form->isValid($_POST)) {
                    $config->saveConfig(
                        'frmuserlogin',
                        'numberOfLastLoginDetails',
                        $form->getElement('numberOfLastLoginDetails')->getValue()
                    );
                    $config->saveConfig(
                        'frmuserlogin',
                        'expiredTimeOfLoginDetails',
                        $form->getElement('expiredTimeOfLoginDetails')->getValue()
                    );
                    $config->saveConfig(
                        'frmuserlogin',
                        'update_active_details',
                        $form->getElement('enableActiveDevices')->getValue()
                    );
                    exit(json_encode(array('result' => true)));
                }
            }
        }else if($currentSection==2){

            OW::getDocument()->setTitle(OW::getLanguage()->text('frmuserlogin', 'admin_page_heading'));
            OW::getDocument()->setHeading(OW::getLanguage()->text('frmuserlogin', 'admin_page_heading'));
            $removeAllCookieForm = new Form('remove_cookies');

            $submitField = new Submit('submit');
            $removeAllCookieForm->addElement($submitField);
            $this->addForm($removeAllCookieForm);
            $removeAllCookieForm->setAjax();
            $removeAllCookieForm->setAjaxResetOnSuccess(false);
            $removeAllCookieForm->setAction(OW::getRouter()->urlForRoute('frmuserlogin.admin.currentSection',['currentSection'=>2]));
            $removeAllCookieForm->bindJsFunction(
                Form::BIND_SUCCESS,
                'function(data){if(data.result){OW.info("'.OW::getLanguage()->text(
                    'frmuserlogin',
                    'setting_saved'
                ).'");}else{OW.error("Parser error");}}'
            );
            $js = new UTIL_JsGenerator();
            $js->jQueryEvent('#button-confirm-remove-all-cookies', 'click', 'if ( !confirm("'.OW::getLanguage()->text('frmuserlogin', 'confirm_delete_all_users_cookies').'") ) return false;');
            OW::getDocument()->addOnloadScript($js);
            if (OW::getRequest()->isAjax()) {
                if ($removeAllCookieForm->isValid($_POST)) {
                    FRMUSERLOGIN_BOL_Service::getInstance()->deleteAllUsersActiveCookies();
                    $deleteAllUsersCookiesEvent = new OW_Event('delete.all.users.active.cookies');
                    OW_EventManager::getInstance()->trigger($deleteAllUsersCookiesEvent);
                    exit(json_encode(array('result' => true)));
                }
            }
        }
    }
}
