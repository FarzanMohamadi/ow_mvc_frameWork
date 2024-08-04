<?php
/**
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_BaseDocument extends OW_ActionController
{

    public function alertPage()
    {
        $text = OW::getSession()->get('baseAlertPageMessage');
        if ( empty($text) )
        {
            throw new Redirect404Exception();
        }
        $this->assign('text', $text);
        OW::getSession()->delete('baseMessagePageMessage');
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_INDEX));
    }

    public function confirmPage()
    {
        $text = OW::getSession()->get('baseConfirmPageMessage');
        if ( empty($text) )
        {
            throw new Redirect404Exception();
        }
        $this->assign('text', OW::getSession()->get('baseConfirmPageMessage'));
        OW::getSession()->delete('baseConfirmPageMessage');

        $back =  ( empty($_GET['back_uri']) )?'':$_GET['back_uri'];
        $this->assign('okBackUrl', OW::getRequest()->buildUrlQueryString(OW_URL_HOME . urldecode($back), array('confirm-result' => 1)));
        $this->assign('clBackUrl', OW::getRequest()->buildUrlQueryString(OW_URL_HOME . urldecode($back), array('confirm-result' => 0)));

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_INDEX));
    }

    public function page404()
    {
        OW::getResponse()->setHeader('HTTP/1.0', '404 Not Found');
        OW::getResponse()->setHeader('Status', '404 Not Found');
        $this->setPageTitle(OW::getLanguage()->text('base', 'base_document_404_title'));
        $this->setDocumentKey('base_page404');
    }

    public function page403( array $params )
    {
        $language = OW::getLanguage();
        OW::getResponse()->setHeader('HTTP/1.0', '403 Forbidden');
        OW::getResponse()->setHeader('Status', '403 Forbidden');
        $this->setPageHeading($language->text('base', 'base_document_403_heading'));
        $this->setPageTitle($language->text('base', 'base_document_403_title'));
        $this->setDocumentKey('base_page403');
        $this->assign('message', !empty($params['message']) ? $params['message'] : $language->text('base', 'base_document_403'));
    }

    public function maintenance()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            //redirect user to home page if maintenance is disabled
            if(OW::getConfig()->getValue('base','maintenance') == '0') {
                OW::getApplication()->redirect(OW_URL_HOME);
            }
            OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate('blank'));
            if (!empty($_COOKIE['adminToken']) && trim($_COOKIE['adminToken']) == OW::getConfig()->getValue('base', 'admin_cookie')) {
                $this->assign('disableMessage', OW::getLanguage()->text('base', 'maintenance_disable_message', array('url' => OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('static_sign_in'), array('back-uri' => urlencode('admin/pages/maintenance'))))));
            }
        }
        else
        {
            exit('{}');
        }
    }

    public function splashScreen()
    {
        if ( !(bool) OW::getConfig()->getValue('base', 'splash_screen') )
        {
            throw new Redirect404Exception();
        }
        if ( isset($_GET['agree']) )
        {
            setcookie('splashScreen', 1, time() + 3600 * 24 * 30, '/');
            if ( !empty($_GET['back_uri']) )
            {
                if(strpos( $_GET['back_uri'], ":") === false ) {
                    $this->redirect($_GET['back_uri']);
                }
            }
            $this->redirect(OW::getRouter()->urlForRoute(''));
        }

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate('blank'));
        $this->assign('submit_url', OW::getRequest()->buildUrlQueryString(null, array('agree' => 1)));

        $leaveUrl = OW::getConfig()->getValue('base', 'splash_leave_url');

        if ( !empty($leaveUrl) )
        {
            $this->assign('leaveUrl', $leaveUrl);
        }
    }

    public function passwordProtection()
    {
        $form = new Form('password_protection');
        $form->setAjax(true);
        $form->setAction(OW::getRouter()->urlFor('BASE_CTRL_BaseDocument', 'passwordProtection'));
        $form->setAjaxDataType(Form::AJAX_DATA_TYPE_SCRIPT);

        $password = new PasswordField('password');
        $form->addElement($password);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('base', 'password_protection_submit_label'));
        $form->addElement($submit);
        $this->addForm($form);

        if ( OW::getRequest()->isAjax() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            $guestPasswordData=json_decode(OW::getConfig()->getValue('base', 'guests_can_view_password'),true);
            $password = $guestPasswordData['guestPassword'];
            $data['password'] = FRMSecurityProvider::getInstance()->hashSha256Data($guestPasswordData['guestSalt']. $data['password']);
            if ( !empty($data['password']) && $data['password'] === $password )
            {
                setcookie('base_password_protection', UTIL_String::getRandomString(), (time() + 86400 * 30), '/');
                echo "OW.info('" . OW::getLanguage()->text('base', 'password_protection_success_message') . "');window.location.reload();";
            }
            else
            {
                echo "OW.error('" . OW::getLanguage()->text('base', 'password_protection_error_message') . "');";
            }
            exit;
        }

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_INDEX));
    }

    public function installCompleted()
    {
        if(!OW::getUser()->isAdmin()){
            throw new Redirect404Exception();
        }
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_INDEX));
    }

    public function redirectToMobile()
    {
        $urlToRedirect = OW::getRouter()->getBaseUrl();

        if ( !empty($_GET['back-uri']) )
        {
            if(strpos( $_GET['back-to'], ":") === false ) {
                $urlToRedirect .= urldecode($_GET['back-uri']);
            }
        }
        
        OW::getApplication()->redirect($urlToRedirect, OW::CONTEXT_MOBILE);
    }

    public function authorizationFailed( array $params )
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('base', 'base_document_auth_failed_heading'));
        $this->setPageTitle($language->text('base', 'base_document_auth_failed_heading'));
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCtrlViewDir() . 'authorization_failed.html');

        $this->assign('message', !empty($params['message']) ? $params['message'] : null);
    }
//    public function tos()
//    {
//        $language = OW::getLanguage();
//        $this->setPageHeading($language->text('base', 'terms_of_use_page_heading'));
//        $this->setPageTitle($language->text('base', 'terms_of_use_page_heading'));
//        $this->assign('content', $language->text('base', 'terms_of_use_page_content'));
//
//
//        $document = BOL_DocumentDao::getInstance()->findStaticDocument('terms-of-use');
//
//        if ( $document !== null )
//        {
//            $languageService = BOL_LanguageService::getInstance(false);
//            $languageId = $languageService->getCurrent()->getId();
//            $prefix = $languageService->findPrefix('base');
//
//            $key = $languageService->findKey('base', 'terms_of_use_page_heading');
//
//            if( $key === null )
//            {
//                $key = new BOL_LanguageKey();
//                $key->setKey('terms_of_use_page_heading');
//                $key->setPrefixId($prefix->getId());
//                $languageService->saveKey($key);
//            }
//
//            $value = $languageService->findValue($languageId, $key->getId());
//            $value->setValue($language->text('base', "local_page_title_{$document->getKey()}"));
//
//            $key = $languageService->findKey('base', 'terms_of_use_page_content');
//
//            if( $key === null )
//            {
//                $key = new BOL_LanguageKey();
//                $key->setKey('terms_of_use_page_content');
//                $key->setPrefixId($prefix->getId());
//                $languageService->saveKey($key);
//            }
//
//            $value = $languageService->findValue($languageId, $key->getId());
//            $value->setValue($language->text('base', "local_page_content_{$document->getKey()}"));
//
//            $key = $languageService->findKey('base', 'terms_of_use_page_meta');
//
//            if( $key === null )
//            {
//                $key = new BOL_LanguageKey();
//                $key->setKey('terms_of_use_page_meta');
//                $key->setPrefixId($prefix->getId());
//                $languageService->saveKey($key);
//            }
//
//            $value = $languageService->findValue($languageId, $key->getId());
//            $value->setValue($language->text('base', "local_page_meta_tags_{$document->getKey()}"));
//
//            $menuItem = BOL_NavigationService::getInstance()->findMenuItemByDocumentKey($document->getKey());
//
//        }
//    }
}
