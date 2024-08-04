<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmguidedtour
 * @since 1.0
 */
class FRMGUIDEDTOUR_BOL_Service
{
    private static $classInstance;
    private $frmguidedtourDao;
    const MOBILE_MODE = 1;
    const DESKTOP_MODE = 0;
    public static $pageMap = array(
        'index' => 'index',
        'dashboard' => 'dashboard',
        'groups' => 'groups',
        'profile' => '',
        'competitions' => 'competitions',
        'events' => 'events/latest',
        'blogs' => 'blogs',
        'photo' => 'photo/viewlist/latest/photos',
        'video' => 'video',
        'news' => 'news',
        'hashtag' => 'hashtag',
        'questions' => 'questions',
        'frmterms' => 'frmterms',
        'frmcontact' => 'frmcontact',
        'active-sessions' => 'active-sessions',
        'frmquestions' => 'frmquestions',
    );
    public static $pageMapMobile = array(
        'userGroups' => 'frmmainpage/user-groups',
        'friends' => 'frmmainpage/friends',
        'dashboard' => 'frmmainpage/dashboard',
        'mailbox' => 'frmmainpage/mailbox',
        'videos' => 'frmmainpage/videos',
        'photos' => 'frmmainpage/photos',
        'news' => 'news',
        'hashtag' => 'hashtag',
        'questions' => 'questions',
        'frmterms' => 'frmterms',
        'frmcontact' => 'frmcontact',
        'notification' => 'frmmainpage/notifications',
        'setting' => 'frmmainpage/settings',
        'chatGroups' => 'frmmainpage/chats-groups',
    );

    public static $pluginMapMobile = array(
        'frmmainpage/user-groups' => 'groups',
        'frmmainpage/friends' => 'friends',
        'frmmainpage/mailbox' => 'mailbox',
        'news' => 'frmnews',
        'hashtag' => 'frmhashtag',
        'questions' => 'questions',
        'frmterms' => 'frmterms',
        'frmcontact' => 'frmcontactus',
        'frmmainpage/chats-groups' => 'frmmainpage',
        'frmmainpage/settings' => 'frmmainpage',
        'frmmainpage/videos' => 'video',
        'frmmainpage/photos' => 'photo',
        'frmmainpage/notifications' => 'notifications',
        'frmmainpage/dashboard' => 'newsfeed'
    );

    public static $pluginMap = array(
        'dashboard' => 'newsfeed',
        'groups' => 'groups',
        'competitions' => 'frmcompetition',
        'events/event_my' => 'frmeventplus',
        'events/latest' => 'event',
        'blogs' => 'blogs',
        'photo/viewlist/latest/photos' => 'photo',
        'video' => 'video',
        'news' => 'frmnews',
        'hashtag' => 'frmhashtag',
        'questions' => 'questions',
        'frmterms' => 'frmterms',
        'frmcontact' => 'frmcontactus',
        'active-sessions' => 'frmuserlogin',
        'frmquestions' => 'frmquestions',
    );
    public static $pageIdMap = array(
        'frmmainpage/user-groups' => 'user-groups',
        'frmmainpage/friends' => 'friends',
        'frmmainpage/mailbox' => 'mailbox',
        'frmmainpage/chats-groups' => 'chatGroups',
        'frmmainpage/settings' => 'settings',
        'frmmainpage/videos' => 'videos',
        'frmmainpage/photos' => 'photos',
        'frmmainpage/notifications' => 'notifications',
        'frmmainpage/dashboard' => 'dashboard'
        );
    private function isPageActive($page, $type)
    {
        $values = json_decode(OW::getConfig()->getValue("frmmainpage", "disables"),true);
        $pluginKey = null;
        if ($type == $this::MOBILE_MODE) {
            if ($page == self::$pageMapMobile['dashboard'] || $page == self::$pageMapMobile['setting']) {
                return true;
            }
            if (isset(self::$pluginMapMobile[$page])) {
                $pluginKey = self::$pluginMapMobile[$page];
            }
        }
        if ($type == $this::DESKTOP_MODE) {
            if ($page == self::$pageMap['index'] || $page == self::$pageMap['profile']) {
                return true;
            }
            if (isset(self::$pluginMap[$page])) {
                $pluginKey = self::$pluginMap[$page];
            }
        }

        if ($pluginKey != null && FRMSecurityProvider::checkPluginActive($pluginKey, true)) {
            if (!isset(self::$pageIdMap[$page])) {
                return true;
            }
            if (isset($values) && !in_array(self::$pageIdMap[$page], $values)) {
                return true;
            }
        }
        return false;
    }

    private function getNextPage($currentPage, $type = 0)
    {
        if ($type == $this::DESKTOP_MODE) {
            $pages = array_keys(self::$pageMap);
            $values = array_values(self::$pageMap);
            $index = array_search($currentPage, $values) + 1;
            while ($index < sizeof($pages) && !$this->isPageActive(self::$pageMap[$pages[$index]], $this::DESKTOP_MODE)) {
                $index++;
            }
            if ($index < sizeof($pages)) {
                return OW_URL_HOME . self::$pageMap[$pages[$index]];
            }
        }
        if ($type == $this::MOBILE_MODE) {
            $pages = array_keys(self::$pageMapMobile);
            $values = array_values(self::$pageMapMobile);
            $index = array_search($currentPage, $values) + 1;
            while ($index < sizeof($pages) && !$this->isPageActive(self::$pageMapMobile[$pages[$index]], $this::MOBILE_MODE)) {
                $index++;
            }
            if ($index < sizeof($pages)) {
                return OW_URL_HOME . self::$pageMapMobile[$pages[$index]];
            }
        }
        return '';
    }

    private function getPreviousPage($currentPage, $type = 0)
    {
        if ($type == $this::DESKTOP_MODE) {
            $pages = array_keys(self::$pageMap);
            $values = array_values(self::$pageMap);
            $index = array_search($currentPage, $values) - 1;
            while ($index >= 0 && !$this->isPageActive(self::$pageMap[$pages[$index]], $this::DESKTOP_MODE)) {
                $index--;
            }
            if ($index >= 0) {
                return OW_URL_HOME . self::$pageMap[$pages[$index]];
            }
        }
        if ($type == $this::MOBILE_MODE) {
            $pages = array_keys(self::$pageMapMobile);
            $values = array_values(self::$pageMapMobile);
            $index = array_search($currentPage, $values) - 1;
            while ($index >= 0 && !$this->isPageActive(self::$pageMapMobile[$pages[$index]], $this::MOBILE_MODE)) {
                $index--;
            }
            if ($index >= 0) {
                return OW_URL_HOME . self::$pageMapMobile[$pages[$index]];
            }
        }
        return '';
    }

    private function __construct()
    {
        $this->frmguidedtourDao=FRMGUIDEDTOUR_BOL_UserGuideDao::getInstance();
    }
    public static function getInstance()
    {
        if (self::$classInstance === null) {

            //Initial assignments
            self::$classInstance = new self();
            if (OW::getUser()->isAuthenticated() && OW::getUser()->getUserObject() !=null) {
                self::$pageMap['profile'] = "user/" . OW::getUser()->getUserObject()->getUsername();
                if(FRMSecurityProvider::checkPluginActive('frmeventplus', true)){
                    self::$pageMap['events'] = "events/event_my";
                }
            }
        }
        return self::$classInstance;
    }

    public function onBeforeDocumentRender()
    {
        $this->setKeysForJs();
        $this->setStatics();
        $this->initializeGuide();
    }

    public function onBeforeMobileDocumentRender()
    {
        $this->setKeysForJs();
        $this->setMobileStatics();
        $this->initializeGuide();
    }

    public function setKeysForJs()
    {
        $language = OW::getLanguage();
        $language->addKeyForJs('frmguidedtour', 'button_next');
        $language->addKeyForJs('frmguidedtour', 'button_prev');
        $language->addKeyForJs('frmguidedtour', 'button_nextPage');
        $language->addKeyForJs('frmguidedtour', 'button_previousPage');
        $language->addKeyForJs('frmguidedtour', 'button_skip');
        $language->addKeyForJs('frmguidedtour', 'index_guideLink');
        $language->addKeyForJs('frmguidedtour', 'guide_title');
        $language->addKeyForJs('frmguidedtour', 'button_activateGuideline');
        $language->addKeyForJs('frmguidedtour', 'end_guided_tour');
    }

    public function setMobileStatics()
    {
        $js1 = "; var frmgt_home_url = \"" . OW_URL_HOME . '";';
        $js2 = "; var frmgt_ajax_seen_url = \"" . OW::getRouter()->urlForRoute('frmguidedtour.setSeen') . '";';
        $js3 = "; var frmgt_ajax_unseen_url = \"" . OW::getRouter()->urlForRoute('frmguidedtour.setUnseen') . '";';
        $update_status_url = "; var frmgt_ajax_update_status_url = \"" . OW::getRouter()->urlForRoute('frmguidedtour.setGuideSeenStatus') . '";';

        OW::getDocument()->addScriptDeclarationBeforeIncludes($js1 . $js2 . $js3 . $update_status_url);
        $jsUrl = OW::getPluginManager()->getPlugin('frmguidedtour')->getStaticJsUrl();
        OW::getDocument()->addScript($jsUrl . 'guideLinkActivatorMobile.js');

    }

    public function setStatics()
    {
        $js1 = "; var frmgt_ajax_seen_url = \"" . OW::getRouter()->urlForRoute('frmguidedtour.setSeen') . '";';
        $js2 = "; var frmgt_ajax_unseen_url = \"" . OW::getRouter()->urlForRoute('frmguidedtour.setUnseen') . '";';
        $update_status_url = "; var frmgt_ajax_update_status_url = \"" . OW::getRouter()->urlForRoute('frmguidedtour.setGuideSeenStatus') . '";';

        OW::getDocument()->addScriptDeclarationBeforeIncludes($js1 . $js2 . $update_status_url);

        $css = '
    a.ow_ic_guidedtour.console_item_guidedtour {
        background-image: url("' . OW::getPluginManager()->getPlugin('frmguidedtour')->getStaticCssUrl() . 'question-mark.svg' . '") ;
        background-size: contain;
    
        background-repeat: no-repeat;
    font-size: 0px;
    width: 20px;
    height: 22px;
    display: inline-block;}';
        OW::getDocument()->addStyleDeclaration($css);
        $jsUrl = OW::getPluginManager()->getPlugin('frmguidedtour')->getStaticJsUrl();
        OW::getDocument()->addScript($jsUrl . 'guideLinkActivator.js');
    }

    public function initializeGuide()
    {
        $user = OW::getUser()->getId();
        if (OW::getUser()->isAuthenticated() && !BOL_QuestionService::getInstance()->getEmptyRequiredQuestionsList($user)) {
            $emailVerified = OW::getUser()->getUserObject()->getEmailVerify();
            $isConfirmationRequired =  OW::getConfig()->getValue('base', 'confirm_email');
            if ($emailVerified == true || $isConfirmationRequired != "1") {  // Checks for case when email confirmation is required
                $userGuide = FRMGUIDEDTOUR_BOL_UserGuideDao::getInstance();

                $jsUrl = OW::getPluginManager()->getPlugin('frmguidedtour')->getStaticJsUrl();
                $cssUrl = OW::getPluginManager()->getPlugin('frmguidedtour')->getStaticCssUrl();
                OW::getDocument()->addStyleSheet($cssUrl . 'bootstrap-tour-standalone.css');
                OW::getDocument()->addStyleSheet($cssUrl . 'guidedTour.css');
                OW::getDocument()->addScript($jsUrl . 'bootstrap-tour-standalone.min.js');
                OW::getDocument()->addScript($jsUrl . 'guidedTour.js');

                if (OW::getApplication()->isMobile()) {
                    OW::getDocument()->addOnloadScript($this->createScriptForOpenMobileGuideManually($this->createMobileGuide()));
                }
                else{
                    OW::getDocument()->addOnloadScript($this->createScriptForOpenGuideManually($this->createGuide()));
                }

                $userStatus = $userGuide->getIntroductionSeenByUser($user);
                $userStatus = $userStatus == null ? 0 : $userStatus;
                if($userStatus == 0 || $userStatus == 1) {
                    if (OW::getApplication()->isMobile()) {
                        OW::getDocument()->addOnloadScript($this->createScript($this->createMobileGuide(), $userStatus));
                    }
                    else{
                        OW::getDocument()->addOnloadScript($this->createScript($this->createGuide(), $userStatus));
                    }
                    $userGuide->updateSeenStatus($user, 2);
                }
            }
        }
    }

    public function initAddrJson($steps, $pluginName, $type = 0)
    {
        $addr_json = array();
        if ($type == $this::DESKTOP_MODE) {
            $addr_json["nextPageAddr"] = self::getNextPage(self::$pageMap[$pluginName], $this::DESKTOP_MODE);
            $addr_json["previousPageAddr"] = self::getPreviousPage(self::$pageMap[$pluginName], $this::DESKTOP_MODE);
        }
        if ($type == $this::MOBILE_MODE) {
            $addr_json["nextPageAddr"] = self::getNextPage(self::$pageMapMobile[$pluginName], $this::MOBILE_MODE);
            $addr_json["previousPageAddr"] = self::getPreviousPage(self::$pageMapMobile[$pluginName], $this::MOBILE_MODE);
        }
        $addr_json["json"] = json_encode($steps);
        return $addr_json;
    }

    public function createScript($addr_json, $seenStatus = 2)
    {
        if (empty($addr_json)) {
            return "";
        }
        $script = 'frmgt_applyGuide(\'' . $addr_json["json"] . '\',\'' . $addr_json["nextPageAddr"] . '\',\'' . $addr_json["previousPageAddr"]  . '\',' . $seenStatus . ')';
        return $script;
    }

    public function createScriptForOpenGuideManually($addr_json)
    {
        if (empty($addr_json)) {
            return '$(document).on("click", "a.console_item_guidedtour", function () {  $.confirm("'. OW::getLanguage()->text('frmguidedtour','page_has_no_guide') .'"); })';
        }
        $script = '$(document).on("click", "a.console_item_guidedtour", function () { frmgt_applyGuide(\'' . $addr_json["json"] . '\',\'' . $addr_json["nextPageAddr"] . '\',\'' . $addr_json["previousPageAddr"]  . '\',' . 1 . '); })';
        return $script;
    }

    public function createScriptForOpenMobileGuideManually($addr_json)
    {
        if (empty($addr_json)) {
            $script = '$(document).on("ready", function () {$(document).on("click", "li.owm_nav_left_item", function (e) {  if ( $($(this).find("a")).attr("href").slice(-15) === "showGuideMobile"){ $.alert(\''. OW::getLanguage()->text("frmguidedtour", "page_unavailable") .'\'); e.preventDefault(); $("a#owm_header_left_btn").click() } }); });';
        }
        else{$script = '$(document).on("ready", function () {$(document).on("click", "li.owm_nav_left_item", function (e) {  if ( $($(this).find("a")).attr("href").slice(-15) === "showGuideMobile"){ e.preventDefault(); frmgt_applyGuide(\'' . $addr_json["json"] . '\',\'' . $addr_json["nextPageAddr"] . '\',\'' . $addr_json["previousPageAddr"]  . '\',' . 1 . '); $("a#owm_header_left_btn").click() } }); });';
        }
        return $script;
    }

    public function getMobilePage($addr = null)
    {
        if (!$addr) {
            $addr = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            //Find the address of the first menu item when address is not set
        }

        if ($addr === OW_URL_HOME . self::$pageMapMobile['news']) {
            return self::$pageMapMobile['news'];
        }
        if ($addr === OW_URL_HOME . self::$pageMapMobile['userGroups']) {
            return self::$pageMapMobile['userGroups'];
        }
        if ($addr === OW_URL_HOME . self::$pageMapMobile['dashboard']) {
            return self::$pageMapMobile['dashboard'];
        }
        if ($addr === OW_URL_HOME . self::$pageMapMobile['hashtag']) {
            return self::$pageMapMobile['hashtag'];
        }
        if ($addr === OW_URL_HOME . self::$pageMapMobile['questions']) {
            return self::$pageMapMobile['questions'];
        }
        if ($addr === OW_URL_HOME . self::$pageMapMobile['frmterms']) {
            return self::$pageMapMobile['frmterms'];
        }
        if ($addr === OW_URL_HOME . self::$pageMapMobile['frmcontact']) {
            return self::$pageMapMobile['frmcontact'];
        }
        if ($addr === OW_URL_HOME . self::$pageMapMobile['notification']) {
            return self::$pageMapMobile['notification'];
        }
        if ($addr === OW_URL_HOME . self::$pageMapMobile['chatGroups']) {
            return self::$pageMapMobile['chatGroups'];
        }
        if ($addr === OW_URL_HOME . self::$pageMapMobile['videos']) {
            return self::$pageMapMobile['videos'];
        }
        if ($addr === OW_URL_HOME . self::$pageMapMobile['photos']) {
            return self::$pageMapMobile['photos'];
        }
        if ($addr === OW_URL_HOME . self::$pageMapMobile['setting']) {
            return self::$pageMapMobile['setting'];
        }
        if ($addr === OW_URL_HOME . self::$pageMapMobile['friends']) {
            return self::$pageMapMobile['friends'];
        }
        if ($addr === OW_URL_HOME . self::$pageMapMobile['mailbox']) {
            return self::$pageMapMobile['mailbox'];
        }
        return "";
    }

    public function getPage($addr = null)
    {
        if (!$addr) {
            $addr = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            //Find the address of the first menu item when address is not set
        }
        if ($addr === OW_URL_HOME || $addr === OW_URL_HOME . "#") {
            $item = BOL_NavigationService::getInstance()->findFirstLocal(
                BOL_NavigationService::VISIBLE_FOR_MEMBER, OW_Navigation::MAIN);
            $addr = OW::getRouter()->urlForRoute($item->getRoutePath());
        }

        if ($addr === OW::getRouter()->urlForRoute('base_index')) {
            return self::$pageMap['index'];
        }
        if ($addr === OW::getRouter()->urlForRoute('base_member_dashboard')) {
            return self::$pageMap['dashboard'];
        }
        if ($addr === OW_URL_HOME . self::$pageMap['profile']) {
            return self::$pageMap['profile'];
        }
        if ($addr === OW_URL_HOME . self::$pageMap['groups']) {
            return self::$pageMap['groups'];
        }
        if ($addr === OW_URL_HOME . self::$pageMap['competitions']) {
            return self::$pageMap['competitions'];
        }
        if ($addr === OW_URL_HOME . self::$pageMap['events']) {
            return self::$pageMap['events'];
        }
        if ($addr === OW_URL_HOME . self::$pageMap['blogs']) {
            return self::$pageMap['blogs'];
        }
        if ($addr === OW_URL_HOME . self::$pageMap['photo']) {
            return self::$pageMap['photo'];
        }
        if ($addr === OW_URL_HOME . self::$pageMap['video']) {
            return self::$pageMap['video'];
        }
        if ($addr === OW_URL_HOME . self::$pageMap['news']) {
            return self::$pageMap['news'];
        }
        if ($addr === OW_URL_HOME . self::$pageMap['hashtag']) {
            return self::$pageMap['hashtag'];
        }
        if ($addr === OW_URL_HOME . self::$pageMap['questions']) {
            return self::$pageMap['questions'];
        }
        if ($addr === OW_URL_HOME . self::$pageMap['frmterms']) {
            return self::$pageMap['frmterms'];
        }
        if ($addr === OW_URL_HOME . self::$pageMap['frmcontact']) {
            return self::$pageMap['frmcontact'];
        }
            if ($addr === OW_URL_HOME . self::$pageMap['active-sessions']) {
            return self::$pageMap['active-sessions'];
        }
        return "";
    }

    public function createMobileGuide($addr = null)
    {
        if (FRMSecurityProvider::checkPluginActive('frmmainpage', true)) {
            $page = $this->getMobilePage($addr);
            switch ($page) {
                case self::$pageMapMobile['userGroups']: {
                    return $this->createMobileGuide_userGroups();
                }
                case self::$pageMapMobile['videos']: {
                    return $this->createMobileGuide_videos();
                }
                case self::$pageMapMobile['photos']: {
                    return $this->createMobileGuide_photos();
                }
                case self::$pageMapMobile['setting']: {
                    return $this->createMobileGuide_setting();
                }
                case self::$pageMapMobile['chatGroups']: {
                    return $this->createMobileGuide_chatGroups();
                }
                case self::$pageMapMobile['notification']: {
                    return $this->createMobileGuide_notification();
                }
                case self::$pageMapMobile['friends']: {
                    return $this->createMobileGuide_friends();
                }
                case self::$pageMapMobile['dashboard']: {
                    return $this->createMobileGuide_dashboard();
                }
                case self::$pageMapMobile['mailbox']: {
                    return $this->createMobileGuide_mailbox();
                }
                case self::$pageMapMobile['news']: {
                    return $this->createMobileGuide_news();
                }
                case self::$pageMapMobile['hashtag']: {
                    return $this->createMobileGuide_hashtag();
                }
                case self::$pageMapMobile['questions']: {
                    return $this->createMobileGuide_questions();
                }
                case self::$pageMapMobile['frmterms']: {
                    return $this->createMobileGuide_frmterms();
                }
                case self::$pageMapMobile['frmcontact']: {
                    return $this->createMobileGuide_frmcontact();
                }
                default:
                    return "";
            }
        }

        #For the case when mobile main page is deactivated
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'mobile_info')));

        $addr_json = array();
        $addr_json["nextPageAddr"] = "";
        $addr_json["previousPageAddr"] = "";
        $addr_json["json"] = json_encode($steps);
        return $addr_json;
    }

    public function createMobileGuide_userGroups()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'mobile_groups_info')));

        return $this->initAddrJson($steps, 'userGroups', $this::MOBILE_MODE);
    }

    public function createMobileGuide_chatGroups()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'mobile_chatGroups_info')));

        return $this->initAddrJson($steps, 'chatGroups', $this::MOBILE_MODE);
    }
    public function createMobileGuide_notification()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'mobile_notification_info')));

        return $this->initAddrJson($steps, 'notification', $this::MOBILE_MODE);
    }

    public function createMobileGuide_friends()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'mobile_friends_info')));
        return $this->initAddrJson($steps, 'friends', $this::MOBILE_MODE);
    }

    public function createMobileGuide_dashboard()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'mobile_dashboard_info')));

        return $this->initAddrJson($steps, 'dashboard', $this::MOBILE_MODE);
    }

    public function createMobileGuide_mailbox()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'mobile_mailbox_info')));

        return $this->initAddrJson($steps, 'mailbox', $this::MOBILE_MODE);
    }
    public function createMobileGuide_videos()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'mobile_videos_info')));

        return $this->initAddrJson($steps, 'videos', $this::MOBILE_MODE);
    }

    public function createMobileGuide_photos()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'mobile_photos_info')));

        return $this->initAddrJson($steps, 'photos', $this::MOBILE_MODE);
    }

    public function createMobileGuide_setting()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'mobile_setting_info')));

        return $this->initAddrJson($steps, 'setting', $this::MOBILE_MODE);
    }

    public function createMobileGuide_news()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'news_info')));

        return $this->initAddrJson($steps, 'news', $this::MOBILE_MODE);
    }

    public function createMobileGuide_hashtag()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'hashtag_info')));

        return $this->initAddrJson($steps, 'hashtag', $this::MOBILE_MODE);
    }

    public function createMobileGuide_questions()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'questions_info')));

        return $this->initAddrJson($steps, 'questions', $this::MOBILE_MODE);
    }

    public function createMobileGuide_frmterms()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'frmterms_info')));

        return $this->initAddrJson($steps, 'frmterms', $this::MOBILE_MODE);
    }

    public function createMobileGuide_frmcontact()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'frmcontact_info')));

        return $this->initAddrJson($steps, 'frmcontact', $this::MOBILE_MODE);
    }

    public function createGuide($addr = null)
    {
        $page = $this->getPage($addr);
        switch ($page) {
            case self::$pageMap['index']: {
                return $this->createGuide_index();
            }
            case self::$pageMap['dashboard']: {
                return $this->createGuide_dashboard();
            }
            case self::$pageMap['groups']: {
                return $this->createGuide_groups();
            }
            case self::$pageMap['profile']: {
                return $this->createGuide_profile();
            }
            case self::$pageMap['competitions']: {
                return $this->createGuide_competitions();
            }
            case self::$pageMap['events']: {
                return $this->createGuide_events();
            }
            case self::$pageMap['blogs']: {
                return $this->createGuide_blogs();
            }
            case self::$pageMap['photo']: {
                return $this->createGuide_photo();
            }
            case self::$pageMap['video']: {
                return $this->createGuide_video();
            }
            case self::$pageMap['news']: {
                return $this->createGuide_news();
            }
            case self::$pageMap['hashtag']: {
                return $this->createGuide_hashtag();
            }
            case self::$pageMap['questions']: {
                return $this->createGuide_questions();
            }
            case self::$pageMap['frmterms']: {
                return $this->createGuide_frmterms();
            }
            case self::$pageMap['frmcontact']: {
                return $this->createGuide_frmcontact();
            }
            case self::$pageMap['active-sessions']: {
                return $this->createGuide_active_sessions();
            }
            case self::$pageMap['frmquestions']:{
                return $this->createGuide_frmquestions();
            }
            default:
                return "";
        }
    }

    public function createStep($address, $description, $placement = null)
    {
        $step = array();
        $step["address"] = $address;
        $step["description"] = $description;
        if (!empty($placement))
            $step["placement"] = $placement;
        return $step;
    }

    public function setSeen()
    {
        $userGuide = FRMGUIDEDTOUR_BOL_UserGuideDao::getInstance();
        $userId = OW::getUser()->getId();
        $userGuide->setGuideSeenByUser($userId, true);
    }


    public function updateSeenStatus($seenStatus)
    {
        $userGuide = FRMGUIDEDTOUR_BOL_UserGuideDao::getInstance();
        $userId = OW::getUser()->getId();
        $userGuide->updateSeenStatus($userId, $seenStatus);
    }

    public function setUnseen()
    {
        $userGuide = FRMGUIDEDTOUR_BOL_UserGuideDao::getInstance();
        $userId = OW::getUser()->getId();
        $userGuide->setGuideSeenByUser($userId, false);
    }

    public function echoMarkup($addr)
    {
        $markup = array();
        $jsUrl = OW::getPluginManager()->getPlugin('frmguidedtour')->getStaticJsUrl();
        $cssUrl = OW::getPluginManager()->getPlugin('frmguidedtour')->getStaticCssUrl();
        $bootStrapTourJs_url = $jsUrl . 'bootstrap-tour-standalone.min.js';
        $guidedTourJs_url = $jsUrl . 'guidedTour.js';
        $bootStrapTourCss_url = $cssUrl . 'bootstrap-tour-standalone.css';
        $guidedTourCss_url = $cssUrl . 'guidedTour.css';

        $scripts = [$bootStrapTourJs_url, $guidedTourJs_url];
        if (!empty($scripts)) {
            $markup['scriptFiles'] = $scripts;
        }

        $styleSheets = [$bootStrapTourCss_url, $guidedTourCss_url];
        if (!empty($styleSheets)) {
            $markup['styleSheets'] = $styleSheets;
        }

        $onloadScript = $this->createScript($this->createGuide($addr));
        if (!empty($onloadScript)) {
            $markup['onloadScript'] = $onloadScript;
        } else {
            $markup['onloadScript'] = '$.alert(\'' . OW::getLanguage()->text('frmguidedtour', 'page_unavailable') . '\')';
        }

        return $markup;
    }

    public function createGuide_index()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'index_info')));
        array_push($steps, $this->createStep(
            "a.ow_ic_guidedtour.console_item_guidedtour", OW::getLanguage()->text('frmguidedtour', 'index_guideLink')));
        //Profile
        array_push($steps, $this->createStep(
            "a.ow_console_item_link:eq(4)", OW::getLanguage()->text('frmguidedtour', 'index_profileLink')));
        //Notifications
        array_push($steps, $this->createStep(
            "a.ow_console_item_link:eq(3)", OW::getLanguage()->text('frmguidedtour', 'index_notificationLink')));
        //Chat
        array_push($steps, $this->createStep(
            "a.ow_console_item_link:eq(2)", OW::getLanguage()->text('frmguidedtour', 'index_messages')));
        //Widgets
        array_push($steps, $this->createStep(
            "div.ow_dnd_widget.index-BASE_CMP_UserListWidget", OW::getLanguage()->text('frmguidedtour', 'index_users_widget'), 'right'));
        array_push($steps, $this->createStep(
            "div.ow_dnd_widget.index-GROUPS_CMP_GroupsWidget", OW::getLanguage()->text('frmguidedtour', 'index_groups_widget'), 'top'));
        array_push($steps, $this->createStep(
            "div.ow_dnd_widget.index-PHOTO_CMP_PhotoListWidget", OW::getLanguage()->text('frmguidedtour', 'index_pics_widget'), 'top'));

        return $this->initAddrJson($steps, 'index', $this::DESKTOP_MODE);
    }

    public function createGuide_dashboard()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'dashboard_info')));

        //NewsFeed
        array_push($steps, $this->createStep(
            "div.ow_dnd_widget.dashboard-NEWSFEED_CMP_MyFeedWidget",
            OW::getLanguage()->text('frmguidedtour', 'dashboard_news'), 'top'));

        return $this->initAddrJson($steps, 'dashboard', $this::DESKTOP_MODE);
    }

    public function createGuide_groups()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'groups_info')));
        //My groups tab
        array_push($steps, $this->createStep(
            "li._my", OW::getLanguage()->text('frmguidedtour', 'groups_myGroups'), 'left'));
        //Latest tab
        array_push($steps, $this->createStep(
            "li._latest ", OW::getLanguage()->text('frmguidedtour', 'groups_latest')));
        //Invitations tab
        array_push($steps, $this->createStep(
            "li._invite", OW::getLanguage()->text('frmguidedtour', 'groups_invitations')));

        return $this->initAddrJson($steps, 'groups', $this::DESKTOP_MODE);
    }

    public function createGuide_profile()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'profile_info')));

        array_push($steps, $this->createStep(
            "div.ow_dnd_widget.profile-BASE_CMP_UserViewWidget", OW::getLanguage()->text('frmguidedtour', 'profile_details'), "left"));
        array_push($steps, $this->createStep(
            ".ow_profile_action_toolbar", OW::getLanguage()->text('frmguidedtour', 'profile_toolbar_buttons'), "left"));
        array_push($steps, $this->createStep(
            "div.ow_dnd_widget.profile-NEWSFEED_CMP_UserFeedWidget", OW::getLanguage()->text('frmguidedtour', 'profile_news'), "top"));
        array_push($steps, $this->createStep(
            "div.ow_dnd_widget.profile-BASE_CMP_UserAvatarWidget", OW::getLanguage()->text('frmguidedtour', 'profile_picture')));
        array_push($steps, $this->createStep(
            "div.ow_dnd_widget.profile-BASE_CMP_AboutMeWidget", OW::getLanguage()->text('frmguidedtour', 'profile_aboutMe')));
        array_push($steps, $this->createStep(
            "div.ow_dnd_widget.profile-FRIENDS_CMP_UserWidget", OW::getLanguage()->text('frmguidedtour', 'profile_contacts')));
        array_push($steps, $this->createStep(
            "div.ow_dnd_widget.profile-GROUPS_CMP_UserGroupsWidget", OW::getLanguage()->text('frmguidedtour', 'profile_groups')));

        return $this->initAddrJson($steps, 'profile', $this::DESKTOP_MODE);
    }

    public function createGuide_competitions()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'competitions_info')));

        return $this->initAddrJson($steps, 'competitions', $this::DESKTOP_MODE);
    }

    public function createGuide_events()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'events_info')));
        //My groups tab
        array_push($steps, $this->createStep(
            "li._event_my", OW::getLanguage()->text('frmguidedtour', 'events_myEvents'), 'left'));
        //Latest tab
        array_push($steps, $this->createStep(
            "li._event_general ", OW::getLanguage()->text('frmguidedtour', 'events_latest')));
        //Invitations tab
        array_push($steps, $this->createStep(
            "li._invited", OW::getLanguage()->text('frmguidedtour', 'events_invitations')));

        return $this->initAddrJson($steps, 'events', $this::DESKTOP_MODE);
    }

    public function createGuide_blogs()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'blogs_info')));
        //Latest tab
        array_push($steps, $this->createStep(
            "li._latest", OW::getLanguage()->text('frmguidedtour', 'blogs_latest'), 'left'));
        array_push($steps, $this->createStep(
            "li._top-rated ", OW::getLanguage()->text('frmguidedtour', 'blogs_top_rated'), 'left'));
        array_push($steps, $this->createStep(
            "li._most-discussed ", OW::getLanguage()->text('frmguidedtour', 'blogs_most_discussed'), 'left'));
        array_push($steps, $this->createStep(
            "li._browse-by-tag ", OW::getLanguage()->text('frmguidedtour', 'blogs_browse_by_tag'), 'left'));
        //create tab
        array_push($steps, $this->createStep(
            "span.ow_ic_add", OW::getLanguage()->text('frmguidedtour', 'blogs_create'), 'left'));

        return $this->initAddrJson($steps, 'blogs', $this::DESKTOP_MODE);
    }

    public function createGuide_photo()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'photo_info')));
        //upload and album
        array_push($steps, $this->createStep(
            "div.ow_btn_delimiter.ow_right", OW::getLanguage()->text('frmguidedtour', 'photo_create'), 'left'));

        return $this->initAddrJson($steps, 'photo', $this::DESKTOP_MODE);
    }

    public function createGuide_video()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'video_info')));
        //upload and album
        array_push($steps, $this->createStep(
            "span.ow_ic_add", OW::getLanguage()->text('frmguidedtour', 'video_create')));
        array_push($steps, $this->createStep(
            "div.ow_content_menu_wrap", OW::getLanguage()->text('frmguidedtour', 'video_toolbar_guide'), 'bottom'));

        return $this->initAddrJson($steps, 'video', $this::DESKTOP_MODE);
    }

    public function createGuide_news()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'news_info')));
        //tools
        array_push($steps, $this->createStep(
            "ul.ow_content_menu.clearfix", OW::getLanguage()->text('frmguidedtour', 'news_tools'), 'bottom'));

        array_push($steps, $this->createStep(
            "span.ow_ic_add", OW::getLanguage()->text('frmguidedtour', 'news_create')));

        return $this->initAddrJson($steps, 'news', $this::DESKTOP_MODE);
    }

    public function createGuide_mobileMainPage()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'mainpage_info')));
        //tools
        return $this->initAddrJson($steps, 'frmmainpage', $this::MOBILE_MODE);
    }

    public function createGuide_hashtag()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'hashtag_info')));

        return $this->initAddrJson($steps, 'hashtag', $this::DESKTOP_MODE);
    }

    public function createGuide_questions()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'questions_info')));

        return $this->initAddrJson($steps, 'questions', $this::DESKTOP_MODE);
    }

    public function createGuide_frmterms()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'frmterms_info')));

        return $this->initAddrJson($steps, 'frmterms', $this::DESKTOP_MODE);
    }

    public function createGuide_frmcontact()
    {
        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'frmcontact_info')));

        return $this->initAddrJson($steps, 'frmcontact', $this::DESKTOP_MODE);
    }
    public function createGuide_active_sessions()
    {

        $steps = array();
        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'active-sessions-info')));
        array_push($steps, $this->createStep(
            "li._login", OW::getLanguage()->text('frmguidedtour', 'active-sessions-login'), 'left'));
        array_push($steps, $this->createStep(
            "li._active.active", OW::getLanguage()->text('frmguidedtour', 'active-sessions-active'), 'bottom'));
        array_push($steps, $this->createStep(
            "div.ow_box a.ow_lbutton", OW::getLanguage()->text('frmguidedtour', 'active-sessions-terminate')));
        array_push($steps, $this->createStep(
            ".ow_content table tbody tr:nth-child(3) td:nth-child(4) a.ow_lbutton", OW::getLanguage()->text('frmguidedtour', 'active-sessions-terminate-one-session')));

        return $this->initAddrJson($steps, 'active-sessions', $this::DESKTOP_MODE);
    }

    public function createGuide_frmquestions()
    {
        $steps = array();

        array_push($steps, $this->createStep(
            "", OW::getLanguage()->text('frmguidedtour', 'frmquestions-info')));

        array_push($steps, $this->createStep(
            "li.frmquestions_all", OW::getLanguage()->text('frmguidedtour', 'all-polls')));

        array_push($steps, $this->createStep(
            "li.frmquestions_hottest", OW::getLanguage()->text('frmguidedtour', 'popular-polls')));

        array_push($steps, $this->createStep(
            "li.frmquestions_friends ", OW::getLanguage()->text('frmguidedtour', 'friends_polls')));

        array_push($steps, $this->createStep(
            "li.frmquestions_my", OW::getLanguage()->text('frmguidedtour', 'my_polls')));

        return $this->initAddrJson($steps, 'frmquestions', $this::DESKTOP_MODE);
    }
}