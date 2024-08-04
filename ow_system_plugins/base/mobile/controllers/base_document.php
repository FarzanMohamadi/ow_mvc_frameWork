<?php
/**
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_MCTRL_BaseDocument extends OW_MobileActionController
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
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MobileMasterPage::TEMPLATE_BLANK));
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

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MobileMasterPage::TEMPLATE_BLANK));
    }

    public function page404()
    {
        OW::getResponse()->setHeader('HTTP/1.0', '404 Not Found');
        OW::getResponse()->setHeader('Status', '404 Not Found');
        $this->setPageHeading(OW::getLanguage()->text('base', 'base_document_404_heading'));
        $this->setPageTitle(OW::getLanguage()->text('base', 'base_document_404_title'));
        $this->setDocumentKey('base_page404');
        $this->assign('message', OW::getLanguage()->text('mobile', 'page_is_not_available', array('url' => OW::getRouter()->urlForRoute('base.desktop_version'))));
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

    public function redirectToDesktop()
    {
        $urlToRedirect = OW::getRouter()->getBaseUrl();

        if ( !empty($_GET['back-uri']) )
        {
            if(strpos( $_GET['back-uri'], ":") === false ) {
                $urlToRedirect .= urldecode($_GET['back-uri']);
            }
        }
        OW::getApplication()->redirect($urlToRedirect, OW::CONTEXT_DESKTOP);
    }

    public function staticDocument( $params )
    {
        $navService = BOL_NavigationService::getInstance();

        if ( empty($params['documentKey']) )
        {
            throw new Redirect404Exception();
        }

        $language = OW::getLanguage();
        $documentKey = $params['documentKey'];

        $document = $navService->findDocumentByKey($documentKey);
        
        if ( $document === null )
        {
            throw new Redirect404Exception();
        }

        $menuItem = $navService->findMenuItemByDocumentKey($document->getKey());

        if ( $menuItem !== null )
        {
            if ( !$menuItem->getVisibleFor() || ( $menuItem->getVisibleFor() == BOL_NavigationService::VISIBLE_FOR_GUEST && OW::getUser()->isAuthenticated() ) )
            {
                throw new Redirect404Exception();
            }

            if ( $menuItem->getVisibleFor() == BOL_NavigationService::VISIBLE_FOR_MEMBER && !OW::getUser()->isAuthenticated() )
            {
                throw new AuthenticateException();
            }
        }

        $settings = BOL_MobileNavigationService::getInstance()->getItemSettings($menuItem);

        $this->assign('content', $settings[BOL_MobileNavigationService::SETTING_CONTENT]);
        $this->setPageHeading($settings[BOL_MobileNavigationService::SETTING_TITLE]);
        $this->setPageTitle($settings[BOL_MobileNavigationService::SETTING_TITLE]);
        $this->setDocumentKey($document->getKey());

        // set meta info
        $params = array(
            "sectionKey" => "base.base_pages",
            "entityKey" => "index",
            "title" => $menuItem->prefix .'+'. $menuItem->key . "_title",
            "description" => $menuItem->prefix .'+'. $menuItem->key . "_desc",
            "keywords" => $menuItem->prefix .'+'. $menuItem->key . "_keywords"
        );
        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));

        //set JSON-LD
        $site_name = OW::getConfig()->getValue('base', 'site_name');
        OW::getDocument()->addJSONLD("Article", $site_name, 1, null, null,
            [
                "publisher" => [
                    "@type" => "Organization",
                    "name" => $site_name,
                    "logo" => ["@type"=>"ImageObject","url"=>OW_URL_HOME.'favicon.ico']
                ],
                "headline" => OW::getLanguage()->text('base', 'meta_title_index'),
                "datePublished" => date('Y-m-d'),
                "dateModified" => date('Y-m-d'),
                "articleBody" => OW::getLanguage()->text('base', 'meta_desc_index'),
                "mainEntityOfPage" => OW_URL_HOME
            ]
        );
    }

    public function maintenance()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MobileMasterPage::TEMPLATE_BLANK));
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

        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MobileMasterPage::TEMPLATE_BLANK));
        $this->assign('submit_url', OW::getRequest()->buildUrlQueryString(null, array('agree' => 1)));

        $leaveUrl = OW::getConfig()->getValue('base', 'splash_leave_url');

        if ( !empty($leaveUrl) )
        {
            $this->assign('leaveUrl', $leaveUrl);
        }
    }

    public function passwordProtection()
    {
        $language = OW::getLanguage();

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
            if ( !empty($data['password']) &&  $data['password'] === $password )
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

        OW::getDocument()->setHeading($language->text('base', 'password_protection_login'));
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MobileMasterPage::TEMPLATE_BLANK));
    }

    public function notAvailable()
    {
        $this->assign('message', OW::getLanguage()->text('mobile', 'page_is_not_available', array('url' => OW::getRouter()->urlForRoute('base.desktop_version'))));
    }

    public function authorizationFailed( array $params )
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('base', 'base_document_auth_failed_heading'));
        $this->setPageTitle($language->text('base', 'base_document_auth_failed_heading'));
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir() . 'authorization_failed.html');
        $this->assign('message', !empty($params['message']) ? $params['message'] : null);
    }
}
