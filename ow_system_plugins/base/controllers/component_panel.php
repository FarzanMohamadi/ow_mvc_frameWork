<?php
/**
 * Widget panel
 *
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_ComponentPanel extends OW_ActionController
{
    /**
     *
     * @var BOL_ComponentAdminService
     */
    private $componentAdminService;
    /**
     *
     * @var BOL_ComponentEntityService
     */
    private $componentEntityService;

    public function __construct()
    {
        parent::__construct();
        $this->componentAdminService = BOL_ComponentAdminService::getInstance();
        $this->componentEntityService = BOL_ComponentEntityService::getInstance();

        $controllersTemplate = OW::getPluginManager()->getPlugin('BASE')->getCtrlViewDir() . 'component_panel.html';
        $this->setTemplate($controllersTemplate);
    }

    private function action( $place, $userId, $customizeMode, $customizeRouts, $componentTemplate,$responderController = null)
    {
        $userCustomizeAllowed = (bool) $this->componentAdminService->findPlace($place)->editableByUser;
        $allowEvent = OW::getEventManager()->trigger(new OW_Event('check.allow.customization.byRole',array('place'=>$place)));
        if(isset($allowEvent->getData()['allowed']))
        {
            if($allowEvent->getData()['allowed'] === true)
            {
                $userCustomizeAllowed = $userCustomizeAllowed && true;
            }else{
                $userCustomizeAllowed = false;
            }
        }
        if ( !$userCustomizeAllowed && $customizeMode )
        {
            $this->redirect($customizeRouts['normal']);
        }

        $schemeList = $this->componentAdminService->findSchemeList();

        $state = $this->componentAdminService->findCache($place);
        if ( empty($state) )
        {
            $state = array();
            $state['defaultComponents'] = $this->componentAdminService->findPlaceComponentList($place);
            $state['defaultPositions'] = $this->componentAdminService->findAllPositionList($place);
            $state['defaultSettings'] = $this->componentAdminService->findAllSettingList();
            $state['defaultScheme'] = (array) $this->componentAdminService->findSchemeByPlace($place);

            $this->componentAdminService->saveCache($place, $state);
        }

        $defaultComponents = $state['defaultComponents'];
        $defaultPositions = $state['defaultPositions'];
        $defaultSettings = $state['defaultSettings'];
        $defaultScheme = $state['defaultScheme'];

        if ( $userCustomizeAllowed )
        {
            $userCache = $this->componentEntityService->findEntityCache($place, $userId);

            if ( empty($userCache) )
            {
                $userCache = array();
                $userCache['userComponents'] = $this->componentEntityService->findPlaceComponentList($place, $userId);
                $userCache['userSettings'] = $this->componentEntityService->findAllSettingList($userId);
                $userCache['userPositions'] = $this->componentEntityService->findAllPositionList($place, $userId);

                $this->componentEntityService->saveEntityCache($place, $userId, $userCache);
            }

            $userComponents = $userCache['userComponents'];
            $userSettings = $userCache['userSettings'];
            $userPositions = $userCache['userPositions'];
        }
        else
        {
            $userComponents = array();
            $userSettings = array();
            $userPositions = array();
        }

        if ( empty($defaultScheme) && !empty($schemeList) )
        {
            $defaultScheme = reset($schemeList);
        }

        $componentPanel = new BASE_CMP_DragAndDropEntityPanel($place, $userId, $defaultComponents, $customizeMode, $componentTemplate, $responderController);
        $componentPanel->setAdditionalSettingList(array(
            'entityId' => $userId,
            'entity' => 'user'
        ));

        if ( !empty($customizeRouts) )
        {
            $componentPanel->allowCustomize($userCustomizeAllowed);
            $componentPanel->customizeControlCunfigure($customizeRouts['customize'], $customizeRouts['normal']);
        }

        $componentPanel->setSchemeList($schemeList);
        $componentPanel->setPositionList($defaultPositions);
        $componentPanel->setSettingList($defaultSettings);
        $componentPanel->setScheme($defaultScheme);

        /*
         * This feature was disabled for users
         * if ( !empty($userScheme) )
          {
          $componentPanel->setEntityScheme($userScheme);
          } */

        if ( !empty($userComponents) )
        {
            $componentPanel->setEntityComponentList($userComponents);
        }

        if ( !empty($userPositions) )
        {
            $componentPanel->setEntityPositionList($userPositions);
        }

        if ( !empty($userSettings) )
        {
            $componentPanel->setEntitySettingList($userSettings);
        }

        $this->assign('componentPanel', $componentPanel->render());
    }

    public function dashboard( $paramList )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $this->setPageTitle(OW::getLanguage()->text('base', 'dashboard_heading'));
        $this->setPageDescription(OW::getLanguage()->text('base', 'dashboard_heading') . OW::getConfig()->getValue('base', 'site_name'));
        $this->setPageHeading(OW::getLanguage()->text('base', 'dashboard_heading'));
        $this->setPageHeadingIconClass('ow_ic_house');

        $customize = !empty($paramList['mode']) && $paramList['mode'] == 'customize';

        $place = BOL_ComponentService::PLACE_DASHBOARD;

        $template = $customize ? 'drag_and_drop_entity_panel_customize' : 'drag_and_drop_entity_panel';

        $customizeUrls = array(
            'customize' => OW::getRouter()->urlForRoute('base_member_dashboard_customize', array('mode' => 'customize')),
            'normal' => OW::getRouter()->urlForRoute('base_member_dashboard')
        );

        $userId = OW::getUser()->getId();

        $this->action($place, $userId, $customize, $customizeUrls, $template);

        $controllersTemplate = OW::getPluginManager()->getPlugin('BASE')->getCtrlViewDir() . 'widget_panel_dashboard.html';

        $this->setTemplate($controllersTemplate);

        $this->assign('isAdmin', OW::getUser()->isAdmin());
        $this->assign('isModerator', BOL_AuthorizationService::getInstance()->isModerator());
        
        $this->setDocumentKey('base_user_dashboard');

        if (isset($_GET['replyToUsername']) && isset($_GET['replyToId'])){
            $replyToText = OW::getLanguage()->text('groups', 'in_reply_to', ['author'=>$_GET['replyToUsername']]);
            $js = " 
                $(document).ready(function(){
                    addPostReplyTo(" . $_GET['replyToId'] . ", '" . $replyToText ."');
                });";
            OW::getDocument()->addOnloadScript($js);
        }
    }

    public function myProfile( $paramList )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $displayName = BOL_UserService::getInstance()->getDisplayName(OW::getUser()->getId());
        $this->setPageTitle(OW::getLanguage()->text('base', 'my_profile_title', array('username' => $displayName)));

        $this->setPageTitle(OW::getLanguage()->text('base', 'profile_view_title', array('username' => $displayName)));
        OW::getDocument()->setDescription(OW::getLanguage()->text('base', 'profile_view_description', array('username' => $displayName)));

        $customize = !empty($paramList['mode']) && $paramList['mode'] == 'customize';

        if ( $customize )
        {
            OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'base', 'main_menu_my_profile');
        }

        $place = BOL_ComponentService::PLACE_PROFILE;

        $template = $customize ? 'drag_and_drop_entity_panel_customize' : 'drag_and_drop_entity_panel';

        $customizeUrls = array(
            'customize' => OW::getRouter()->urlForRoute('base_member_profile_customize', array('mode' => 'customize')),
            'normal' => OW::getRouter()->urlForRoute('base_member_profile')
        );

        $userId = OW::getUser()->getId();

        $this->action($place, $userId, $customize, $customizeUrls, $template);
        $this->setDocumentKey("base_user_profile");
    }

    public function profile( $paramList )
    {
        $userService = BOL_UserService::getInstance();
        /* @var $userDao BOL_User */
        $userDto = $userService->findByUsername($paramList['username']);

        if ( $userDto === null )
        {
            throw new Redirect404Exception();
        }

        if ( $userDto->id == OW::getUser()->getId() )
        {
            $this->myProfile($paramList);

            return;
        }

        if ( !OW::getUser()->isAuthorized('base', 'view_profile') && !OW::getUser()->isAuthorized('base') && !OW::getUser()->isAdmin())
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'view_profile');
            throw new AuthorizationException($status['msg']);
        }

        if (!OW::getUser()->isAuthorized('base') && !OW::getUser()->isAdmin()) {
            if (OW::getUser()->isAuthenticated() && OW::getUser()->getId() != $userDto->id) {
                if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userDto->id) ) {
                    throw new AuthorizationException(OW::getLanguage()->text('base', 'authorization_failed_feedback'));
                }
            }
        }

        $eventParams = array(
            'action' => 'base_view_profile',
            'ownerId' => $userDto->id,
            'viewerId' => OW::getUser()->getId()
        );

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $ex )
        {
            $exception = new RedirectException(OW::getRouter()->urlForRoute('base_user_privacy_no_permission', array('username' => $userDto->username)));

            throw $exception;
        }

        $displayName = BOL_UserService::getInstance()->getDisplayName($userDto->id);

        $this->assign('isSuspended', $userService->isSuspended($userDto->id));
        $this->assign('isAdminViewer', OW::getUser()->isAuthorized('base'));

        $place = BOL_ComponentService::PLACE_PROFILE;

        $template = 'drag_and_drop_entity_panel';

        $this->action($place, $userDto->id, false, array(), $template);

        $controllersTemplate = OW::getPluginManager()->getPlugin('BASE')->getCtrlViewDir() . 'widget_panel_profile.html';
        $this->setTemplate($controllersTemplate);

        $this->setDocumentKey('base_profile_page');

        $vars = BOL_SeoService::getInstance()->getUserMetaInfo($userDto);

        // set meta info
        $params = array(
            "sectionKey" => "base.users",
            "entityKey" => "userPage",
            "title" => "base+meta_title_user_page",
            "description" => "base+meta_desc_user_page",
            "keywords" => "base+meta_keywords_user_page",
            "vars" => $vars,
            "image" => BOL_AvatarService::getInstance()->getAvatarUrl($userDto->getId(), 2)
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));

        //set JSON-LD
        OW::getDocument()->addJSONLD("Person", $displayName, false, $userService->getUserUrl($userDto->getId()), $params['image'],
            [
                "email"=> "mailto:".$userDto->getEmail(),
            ]
        );
    }

    public function privacyMyProfileNoPermission( $params )
    {
        $username = $params['username'];

        $user = BOL_UserService::getInstance()->findByUsername($username);
        $suspendStatus = BOL_UserService::getInstance()->findSupsendStatusForUserList(array($user->getId()));

        if ( $user === null || (isset($suspendStatus) && isset($suspendStatus[$user->getId()]) && $suspendStatus[$user->getId()]))
        {
            throw new Redirect404Exception();
        }

        $eventParams = array(
            'action' => 'base_view_profile',
            'ownerId' => $user->id,
            'viewerId' => OW::getUser()->getId()
        );
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
            $backUrl = OW::getRouter()->urlForRoute('base_user_profile', array('username'=>$username));
            $this->redirect($backUrl);
        }
        catch ( RedirectException $ex )
        {
        }
        if ( OW::getSession()->isKeySet('privacyRedirectExceptionMessage') )
        {
            $this->assign('message', OW::getSession()->get('privacyRedirectExceptionMessage'));
        }

        $avatarService = BOL_AvatarService::getInstance();

        $userId = $user->id;

        $this->setPageHeading(OW::getLanguage()->text('base', 'profile_view_heading', array('username' => BOL_UserService::getInstance()->getDisplayName($userId))));
        $this->setPageHeadingIconClass('ow_ic_user');

        $this->assign('avatar', $avatarService->getAvatarUrl($userId, 2));
        $roles = BOL_AuthorizationService::getInstance()->getLastDisplayLabelRoleOfIdList(array($userId));
        $this->assign('role', !empty($roles[$userId]) ? $roles[$userId] : null);

        $this->assign('username', $username);

        $this->assign('avatarSize', OW::getConfig()->getValue('base', 'avatar_big_size'));
        
        $cmp = OW::getClassInstance("BASE_CMP_ProfileActionToolbar", $userId);
        $this->addComponent('profileActionToolbar', $cmp);

        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCtrlViewDir() . 'user_view_privacy_no_permission.html');
    }

    public function index( $paramList )
    {
        $place = BOL_ComponentService::PLACE_INDEX;
        $customize = !empty($paramList['mode']) && $paramList['mode'] == 'customize';
        $allowCustomize = OW::getUser()->isAdmin();
        $template = 'drag_and_drop_index';

        if ( $customize )
        {
            if ( !OW::getUser()->isAuthenticated() )
            {
                throw new AuthenticateException();
            }

            if ( !$allowCustomize )
            {
                $this->redirect(OW::getRouter()->uriForRoute('base_index'));
            }
        }

        if ( $allowCustomize )
        {
            $template = $customize ? 'drag_and_drop_index_customize' : 'drag_and_drop_index';

            if ( $customize )
            {
                OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'base', 'main_menu_index');
            }
        }

        if ( $customize )
        {
            $masterPageFileDir = OW::getThemeManager()->getMasterPageTemplate('dndindex');
            OW::getDocument()->getMasterPage()->setTemplate($masterPageFileDir);
            
            $this->setDocumentKey('base_index_page_customize');
        }
        else
        {
            $this->setDocumentKey('base_index_page');
        }
        
        $schemeList = $this->componentAdminService->findSchemeList();
        $state = $this->componentAdminService->findCache($place);

        if ( empty($state) )
        {
            $state = array();
            $state['defaultComponents'] = $this->componentAdminService->findPlaceComponentList($place);
            $state['defaultPositions'] = $this->componentAdminService->findAllPositionList($place);
            $state['defaultSettings'] = $this->componentAdminService->findAllSettingList();
            $state['defaultScheme'] = (array) $this->componentAdminService->findSchemeByPlace($place);

            $this->componentAdminService->saveCache($place, $state);
        }

        $defaultComponents = $state['defaultComponents'];
        $defaultPositions = $state['defaultPositions'];
        $defaultSettings = $state['defaultSettings'];
        $defaultScheme = $state['defaultScheme'];

        if ( empty($defaultScheme) && !empty($schemeList) )
        {
            $defaultScheme = reset($schemeList);
        }

        $componentPanel = new BASE_CMP_DragAndDropIndex($place, $defaultComponents, $customize, $template);
        $componentPanel->allowCustomize($allowCustomize);

        $customizeUrls = array(
            'customize' => OW::getRouter()->urlForRoute('base_index_customize', array('mode' => 'customize')),
            'normal' => OW::getRouter()->urlForRoute('base_index')
        );

        $componentPanel->customizeControlCunfigure($customizeUrls['customize'], $customizeUrls['normal']);

        $componentPanel->setSchemeList($schemeList);
        $componentPanel->setPositionList($defaultPositions);
        $componentPanel->setSettingList($defaultSettings);
        $componentPanel->setScheme($defaultScheme);

        /* $themeName = OW::getConfig()->getValue('base', 'selectedTheme');
          $sidebarPosition = BOL_ThemeService::getInstance()->findThemeByName($themeName)->getSidebarPosition(); */

        $sidebarPosition = OW::getThemeManager()->getCurrentTheme()->getDto()->getSidebarPosition();
        $componentPanel->setSidebarPosition($sidebarPosition);

        $componentPanel->assign('adminPluginsUrl', OW::getRouter()->urlForRoute('admin_plugins_installed'));

        $this->addComponent('componentPanel', $componentPanel);

        // set meta info
        $params = array(
            "sectionKey" => "base.base_pages",
            "entityKey" => "index",
            "title" => "base+meta_title_index",
            "description" => "base+meta_desc_index",
            "keywords" => "base+meta_keywords_index"
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

    public function ajaxSaveAboutMe()
    {

        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        echo json_encode(BASE_CMP_AboutMeWidget::processForm($_POST));

        exit();
    }

    public function redirectToUserprofile(){
        $userProfileUrl = OW::getRouter()->urlForRoute('base_user_profile', array('username' => OW::getUser()->getUserObject()->username));
        $this->redirect($userProfileUrl);
    }
}