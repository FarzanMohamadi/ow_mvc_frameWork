<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancesearch.classes
 * @since 1.0
 */
class FRMADVANCESEARCH_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    private function __construct()
    {
    }

    public function genericInit()
    {
        $eventManager = OW::getEventManager();
        $eventManager->bind('frmadvancesearch.on_collect_search_items', array($this, 'onCollectSearchItems'));
        $eventManager->bind('frmadvancesearch.on_before_collect_search_items', array($this, 'onBeforeCollectSearchItems'));
    }

    public function init()
    {
        $this->genericInit();

        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onBeforeDocumentRender'));
        $eventManager->bind('console.collect_items', array($this, 'collectItems'));
        $eventManager->bind(OW_EventManager::ON_AFTER_PLUGIN_ACTIVATE, array($this, 'after_plugin_activate'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_PLUGIN_DEACTIVATE, array($this, 'before_plugin_deactivate'));
    }

    public function collectItems(OW_Event $event)
    {
        $isGuestAllowed = (boolean)OW::getConfig()->getValue('frmadvancesearch','show_search_to_guest');
        if(!OW::getUser()->isAuthenticated() && !$isGuestAllowed){
            return;
        }

        $baseConfigs = OW::getConfig()->getValues('base');
        //members only
        if ( !OW::getUser()->isAuthenticated() && (int) $baseConfigs['guests_can_view'] === BOL_UserService::PERMISSIONS_GUESTS_CANT_VIEW)
        {
            return;
        }

        $item = new FRMADVANCESEARCH_CMP_ConsoleSearch();
        $event->addItem($item, 6);
    }

    public function onBeforeDocumentRender(OW_Event $event)
    {
//        if(!OW::getUser()->isAuthenticated()){
//            return;
//        }

        $jsFile = OW::getPluginManager()->getPlugin('frmadvancesearch')->getStaticJsUrl() . 'frmadvancesearch.js';
        OW::getDocument()->addScript($jsFile);

        $cssFile = OW::getPluginManager()->getPlugin('frmadvancesearch')->getStaticCssUrl() . 'frmadvancesearch.css';
        OW::getDocument()->addStyleSheet($cssFile);

        $css = '
    html body div .ow_ic_lens.ow_console_search {
        background-image: url("' . OW::getPluginManager()->getPlugin('frmadvancesearch')->getStaticCssUrl() . 'search.svg' . '") !important;
    }';
        OW::getDocument()->addStyleDeclaration($css);

        $lang = OW::getLanguage();
        $lang->addKeyForJs('frmadvancesearch', 'search_title');
        $lang->addKeyForJs('frmadvancesearch', 'no_data_found');
        $lang->addKeyForJs('frmadvancesearch', 'users');
        $lang->addKeyForJs('frmadvancesearch', 'minimum_two_character');
        $lang->addKeyForJs('frmadvancesearch', 'forum_posts_title');
        $lang->addKeyForJs('frmadvancesearch', 'forum_post_title');
        $lang->addKeyForJs('frmadvancesearch', 'forum_post_group_name');
        $lang->addKeyForJs('frmadvancesearch', 'forum_post_section_name');
    }



    public function after_plugin_activate(OW_Event $event)
    {
        $params = $event->getParams();
        if ( !isset($params['pluginKey']))
            return;
        if( $params['pluginKey'] == "friends" && false){
            $widgetService = BOL_ComponentAdminService::getInstance();
            $widget = $widgetService->addWidget('FRMADVANCESEARCH_MCMP_FriendsSearchWidget', false);
            $placeWidget = $widgetService->addWidgetToPlace($widget, BOL_MobileWidgetService::PLACE_MOBILE_INDEX);
            $widgetService->addWidgetToPosition($placeWidget, BOL_MobileWidgetService::SECTION_MOBILE_MAIN);
        }
    }
    public function before_plugin_deactivate(OW_Event $event)
    {
        $params = $event->getParams();
        if ( !isset($params['pluginKey']))
            return;
        if( $params['pluginKey'] == "friends"){
            BOL_ComponentAdminService::getInstance()->deleteWidget('FRMADVANCESEARCH_MCMP_FriendsSearchWidget');
        }
    }
    public function onBeforeCollectSearchItems(OW_Event $event){
        if(isset($_REQUEST['searchValue']))
        {
            $searchValue = trim($_REQUEST['searchValue']);
            $searchValue =  UTIL_HtmlTag::stripTagsAndJs($searchValue);
            $searchValue = json_encode($searchValue);
            $searchValue = str_replace('"', '', $searchValue);
            $searchValue = str_replace("\\", '\\\\', $searchValue);
            $searchValue = '%"data":{%status%'.$searchValue.'%}%,"actionDto":%';
            $event->setData($searchValue);
        }
    }

    public function getActionString($action) {
        $text = "";
        $activityString = "";
        $actionDataJson = null;
        if(isset($action->data)){
            $actionDataJson = $action->data;
        }

        if($actionDataJson != null){
            $actionDataJson = json_decode($actionDataJson);
        }

        if($actionDataJson != null) {
            if (isset($actionDataJson->string)) {
                if (!isset($actionDataJson->string->key)) {
                    $activityString = $actionDataJson->string;
                } else {
                    $keys = explode('+', $actionDataJson->string->key);
                    $varsArray = array();
                    $vars = empty($actionDataJson->string->vars) ? array() : $actionDataJson->string->vars;
                    foreach ($vars as $key => $var) {
                        $varsArray[$key] = $var;
                    }
                    $string = OW::getLanguage()->text($keys[0], $keys[1], $varsArray);
                    if (!empty($string)) {
                        $activityString = $string;
                    }
                }
            }

            if ($action->format == "image_content") {
                if (isset($actionDataJson->status)) {
                    $text = $actionDataJson->status;
                }
            } else if ($action->format == "text" || $action->format == "content") {
                if (isset($actionDataJson->status)) {
                    $text = $actionDataJson->status;
                }
            }

            if (isset($actionDataJson->photoIdList)) {
                if (isset($actionDataJson->content->vars->status)) {
                    $text = $actionDataJson->content->vars->status;
                }
            }
        }
        if ($text == ""  && $activityString != "") {
            $text = $activityString;
        }

        $stringRenderer = OW::getEventManager()->trigger(new OW_Event('emoji.before_render_string', array('string' => $text)));
        if (isset($stringRenderer->getData()['string'])) {
            $text = ($stringRenderer->getData()['string']);
        }
        return $text;
    }

    public function onCollectSearchItems(OW_Event $event){
        $data = $event->getData();
        $params = $event->getParams();
        $searchValue = '';
        if ( !empty($params['q']) )
        {
            $searchValue = $params['q'];
        }
        $searchValue = strip_tags(UTIL_HtmlTag::stripTags($searchValue));
        $maxCount = empty($params['maxCount'])?10:$params['maxCount'];
        $first= empty($params['first'])?0:$params['first'];
        $first=(int)$first;
        $pageCount=empty($params['count'])?$first+$maxCount:$params['count'];
        $pageCount = (int) $pageCount;
        $selected_section = null;
        if(!empty($params['selected_section']))
            $selected_section = $params['selected_section'];
        if (!isset($selected_section) ||(isset($selected_section) && ($selected_section == OW::getLanguage()->text('frmadvancesearch', 'users_label') || $selected_section == OW_Language::getInstance()->text('frmadvancesearch', 'all_sections')))){

            $userId = OW::getUser()->getId();
            $resultData = array();

            if (!isset($params['do_query']) || $params['do_query']) {
                if (OW::getAuthorization()->isUserAuthorized($userId, 'base', 'search_users') || OW::getUser()->isAdmin()) {
                    $resultData = FRMADVANCESEARCH_CTRL_Search::getInstance()->getUsersBySearchValue($searchValue,
                        true, true, $first, $pageCount);
                } else {
                    $resultData = FRMADVANCESEARCH_CTRL_Search::getInstance()->getUsersBySearchValue($searchValue,
                        true, false, $first, $pageCount);
                }
            }

            $result = array();
            $count = 0;
            $userIdList = array_column($resultData, 'id');
            $userIdListUnique = array_unique($userIdList);
            $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIdListUnique);
            foreach ($resultData as $item) {
                $itemInformation = array();
                $itemInformation['username'] = substr($item['url'], strpos($item['url'], 'user/') + 5);
                $itemInformation['title'] = empty($item['title']) ? $itemInformation['username'] : $item['title'];
                $itemInformation['displayName'] = $displayNames[$item['id']];
                $itemInformation['userUrl'] = $item["url"];
                $itemInformation['id'] = $item['id'];
                $itemInformation['link'] = $item['url'];
                $itemInformation['image'] = $item['src'];
                $itemInformation['imageInfo'] = BOL_AvatarService::getInstance()->getAvatarInfo((int)$item['id'], $item['src']);
                $itemInformation['label'] = OW::getLanguage()->text('frmadvancesearch', 'users_label');
                $result[] = $itemInformation;
                $count++;
                if ($count == $maxCount) {
                    break;
                }
            }
            $data['users'] = array('label' => OW::getLanguage()->text('frmadvancesearch', 'users_label'), 'data' => $result);
        }

        if ( !isset($selected_section) || (isset($selected_section) && ($selected_section == OW::getLanguage()->text('newsfeed', 'auth_group_label') || $selected_section == OW_Language::getInstance()->text('frmadvancesearch', 'all_sections')))){
        //newsfeed
            $resultData = array();
            if (!isset($params['do_query']) || $params['do_query']) {
                $siteFeed = NEWSFEED_BOL_ActionDao::getInstance()->findSiteFeed(array($first, $pageCount, false));

                if (OW::getUser()->isAuthenticated()) {
                    $dashboardFeed = NEWSFEED_BOL_ActionDao::getInstance()->findByUser(OW::getUser()->getId(), array($first, $pageCount, false));
                    $resultData = array_unique(array_merge($siteFeed, $dashboardFeed), SORT_REGULAR);
                } else
                    $resultData = array_unique($siteFeed, SORT_REGULAR);
            }
        $count = 0;
        $result = array();
        foreach($resultData as $item){
            if($item->entityType == "groups-status" &&  !OW::getPluginManager()->isPluginActive('groups'))
                continue;
            $feedData = json_decode($item->data);
            $itemInformation = array();
            $itemInformation ['title'] = $this->getActionString($item);
            if (isset($item->id)) {
                $itemInformation ['link'] = OW::getRouter()->urlForRoute('newsfeed_view_item', array('actionId' => $item->id));
            }
            $itemInformation['label'] = OW::getLanguage()->text('newsfeed', 'auth_group_label');
            $itemInformation['id'] =  $item->id;
            if (isset($feedData->data) && isset($feedData->data->userId)) {
                $id = $feedData->data->userId;
                $itemInformation['userId'] = $id;
                $itemInformation['displayName'] =BOL_UserService::getInstance()->getDisplayName($id);
                $itemInformation['userUrl'] =BOL_UserService::getInstance()->getUserUrl($id);

            }
            if (isset($feedData->time)) {
                $itemInformation['createdDate'] = $feedData->time;
            }

            $itemInformation['emptyImage'] = true;
            $itemInformation['image'] = OW::getPluginManager()->getPlugin('frmadvancesearch')->getStaticUrl() .'img/newsfeed_default_image.svg';

            $result[] = $itemInformation;

            $count++;
            if($count == $maxCount){
                break;
            }
        }

        $data['newsfeed'] = array('label' => OW::getLanguage()->text('newsfeed', 'auth_group_label'), 'data' => $result);
    }

        if ( isset($selected_section) && ($selected_section = 'mentions')){

            //mentions
            $resultData = array();
            if (!isset($params['do_query']) || $params['do_query']) {
//                $siteFeed = NEWSFEED_BOL_ActionDao::getInstance()->findSiteFeed(array($first, $pageCount, false));
//
//                if (OW::getUser()->isAuthenticated()) {
//                    $dashboardFeed = NEWSFEED_BOL_ActionDao::getInstance()->findByUser(OW::getUser()->getId(), array($first, $pageCount, false));
//                    $resultData = array_unique(array_merge($siteFeed, $dashboardFeed), SORT_REGULAR);
//                } else
//                    $resultData = array_unique($siteFeed, SORT_REGULAR);
                $dashboardFeed = NEWSFEED_BOL_ActionDao::getInstance()->findByUser(OW::getUser()->getId(), array($first, $pageCount, false), null, null, null, null, "user-status", $searchValue);
                $resultData = array_unique($dashboardFeed, SORT_REGULAR);
            }
            $count = 0;
            $result = array();
            foreach($resultData as $item){
                if($item->entityType == "groups-status" &&  !OW::getPluginManager()->isPluginActive('groups'))
                    continue;
                $feedData = json_decode($item->data);
                $itemInformation = array();
                $itemInformation ['title'] = $this->getActionString($item);
                if (isset($item->id)) {
                    $itemInformation ['link'] = OW::getRouter()->urlForRoute('newsfeed_view_item', array('actionId' => $item->id));
                }
                $itemInformation['label'] = OW::getLanguage()->text('newsfeed', 'auth_group_label');
                $itemInformation['id'] =  $item->id;
                if (isset($feedData->data) && isset($feedData->data->userId)) {
                    $id = $feedData->data->userId;
                    $itemInformation['userId'] = $id;
                    $itemInformation['displayName'] =BOL_UserService::getInstance()->getDisplayName($id);
                    $itemInformation['userUrl'] =BOL_UserService::getInstance()->getUserUrl($id);

                }
                if (isset($feedData->time)) {
                    $itemInformation['createdDate'] = $feedData->time;
                }

                $itemInformation['emptyImage'] = true;
                $itemInformation['image'] = OW::getPluginManager()->getPlugin('frmadvancesearch')->getStaticUrl() .'img/newsfeed_default_image.svg';

                $result[] = $itemInformation;

                $count++;
                if($count == $maxCount){
                    break;
                }
            }

            $data['mentions'] = array('label' => OW::getLanguage()->text('newsfeed', 'auth_group_label'), 'data' => $result);
        }

        $event->setData($data);
    }
}