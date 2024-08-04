<?php
/**
 * Console notifications section items component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.notifications.mobile.components
 * @since 1.6.0
 */
class NOTIFICATIONS_MCMP_ConsoleItems extends OW_MobileComponent
{
    /**
     * Constructor.
     */
    public function __construct(  $limit, $exclude = null )
    {
        parent::__construct();

        $service = NOTIFICATIONS_BOL_Service::getInstance();
        $userId = OW::getUser()->getId();
        $addToExclude = $service->findIgnoreNotifications();
        if(sizeof($addToExclude) > 0)
        {
            if(isset($exclude)) {
                $exclude = array_merge($exclude, $addToExclude);
            } else{
                $exclude = $addToExclude;
            }
        }
        $notifications = $service->findNotificationList($userId, time(), $exclude, $limit);
        $items = self::prepareData($notifications);
        $this->assign('items', $items);

        $notificationIdList = array();
        foreach ( $items as $id => $item )
        {
            $notificationIdList[] = $id;
        }

        // Mark as viewed
        $service->markNotificationsViewedByUserId($userId);

        $exclude = is_array($exclude) ? array_merge($exclude, $notificationIdList) : $notificationIdList;
        $loadMore = (bool) $service->findNotificationCount($userId, null, $exclude);
        if ( !$loadMore )
        {
            $script = "OWM.trigger('mobile.console_hide_notifications_load_more', {});";
            OW::getDocument()->addOnloadScript($script);
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('notifications')->getStaticJsUrl() . 'notifications.js');
        OW::getDocument()->addStyleSheet( OW_PluginManager::getInstance()->getPlugin("notifications")->getStaticCssUrl() . 'notification.css');
    }

    public static function prepareData( $notifications )
    {
        if ( !$notifications )
        {
            return array();
        }

        $avatars = array();
        $router = OW::getRouter();
        foreach ( $notifications as $notification )
        {
            $data = $notification->getData();

            if(isset($data['url'])) {
                $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ, array('string' => $data['url'])));
                if (isset($stringRenderer->getData()['string'])) {
                    $data['url'] = $stringRenderer->getData()['string'];
                }
            }
            if(isset($data['avatar']['src'])) {
                $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ, array('string' => $data['avatar']['src'])));
                if (isset($stringRenderer->getData()['string'])) {
                    $data['avatar']['src'] = $stringRenderer->getData()['string'];
                }
            }
            if(isset($data['avatar']['url'])) {
                $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ, array('string' => $data['avatar']['url'])));
                if (isset($stringRenderer->getData()['string'])) {
                    $data['avatar']['url'] = $stringRenderer->getData()['string'];
                }
            }
            $avatar = empty($data['avatar']) ? array() : $data['avatar'];

            if ( !empty($data["avatar"]["userId"]) )
            {
                $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($data["avatar"]["userId"]));
                $avatar = $avatarData[$data["avatar"]["userId"]];
            }
            $userDeleted = isset($avatar['urlInfo']['routeName'])
                && $avatar['urlInfo']['routeName']=='base_user_profile'
                && (!isset($avatar['urlInfo']['vars']['username']));

            $notificationAvatarImageSource = isset($avatar['src']) ? $avatar['src'] : null;
            $avatars[$notification->id] = array(
                'src' => $notificationAvatarImageSource,
                'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo((int) $notification->userId, $notificationAvatarImageSource),
                'title' => isset($avatar['title']) ? $avatar['title'] : null,
                'url' => isset($data['avatar']['urlInfo']['routeName']) && !$userDeleted ?
                    $router->urlForRoute($avatar['urlInfo']['routeName'], $avatar['urlInfo']['vars']) : null
            );
        }

        $items = array();

        $userIds = array();
        $groupIds = array();
        $entityTypes = array();
        $entityIds = array();
        foreach ( $notifications as $notification ) {
            $entityTypes[] = $notification->entityType;
            $entityIds[] = $notification->entityId;
            $userIds[] = $notification->userId;
            $notifData = $notification->getData();
            if (isset($notifData['avatar']->userId)) {
                $userIds[] = $notifData['avatar']->userId;
            }
        }

        if (FRMSecurityProvider::checkPluginActive('newsfeed')) {
            $cachedActions = NEWSFEED_BOL_ActionDao::getInstance()->findActionListByEntityIdsAndEntityTypes($entityIds, $entityTypes);
            $cachedActionsByEntity = array();
            foreach ($cachedActions as $cachedAction) {
                $cachedActionsByEntity[$cachedAction->entityType . '-' . $cachedAction->entityId] = $cachedAction;
            }
            $cache['actions_by_entity'] = $cachedActionsByEntity;

            foreach ($cachedActionsByEntity as $action) {
                $data = (array) json_decode($action->data);
                if (isset($data['contextFeedId'])) {
                    $groupIds[] = $data['contextFeedId'];
                }
            }

            if (FRMSecurityProvider::checkPluginActive('groups', true) && !empty($groupIds)) {
                $groups = GROUPS_BOL_GroupDao::getInstance()->findByIdList($groupIds);
                foreach ($groups as $group) {
                    $groupsCacheInfo[$group->id] = $group;
                }
                $cache['groups'] = $groupsCacheInfo;
            }
        }

        $usersCacheInfoById = array();
        $usersCacheInfoByUsername = array();
        $userIds = array_unique($userIds);
        if (sizeof($userIds) > 0) {
            $userList = BOL_UserDao::getInstance()->findByIdList($userIds);
            foreach ($userList as $user) {
                $usersCacheInfoById[$user->id] = $user;
                $usersCacheInfoByUsername[$user->username] = $user;
            }
        }
        $cache['users']['id'] = $usersCacheInfoById;
        $cache['users']['username'] = $usersCacheInfoByUsername;

        foreach ( $notifications as $notification )
        {
            $disabled = false;
            $notificationData=NOTIFICATIONS_CLASS_ConsoleBridge::getInstance()->getEditedData($notification->pluginKey,$notification->entityId,$notification->entityType, $notification->getData(), $cache);
            if(isset($notificationData["string"]["vars"]["status"]))
                $notificationData["string"]["vars"]["status"] = UTIL_String::truncate(UTIL_HtmlTag::stripTags($notificationData["string"]["vars"]["status"]), 200, '...');

            /** @var $notification NOTIFICATIONS_BOL_Notification */
            /*$notifData = $notification->getData();

            if ( isset($notifData['url']) )
            {
                if ( is_array($notifData['url']) && !empty($notifData['url']['routeName']) )
                {
                    $routeVars = isset($notifData['url']['routeVars']) ? $notifData['url']['routeVars'] : array();
                    $notifData['url'] = $router->urlForRoute($notifData['url']['routeName'], $routeVars);
                }
            }*/

            $itemEvent = new OW_Event('mobile.notifications.on_item_render', array(
                'entityType' => $notification->entityType,
                'entityId' => $notification->entityId,
                'pluginKey' => $notification->pluginKey,
                'userId' => $notification->userId,
                'data' =>$notificationData,
                'cache' => $cache,
            ));

            OW::getEventManager()->trigger($itemEvent);
            $item = $itemEvent->getData();
            /*
             * In order to repetitive notifications, some notifications must be shown only in desktop version like friend request
             * because in Desktop version friend request and invitations are in different parts but in mobile version all of the are in one list
             */
            if(isset($item) && isset($item['ignoreNotification']) && $item['ignoreNotification']==true)
            {
                continue;
            }
            if ( !$item ) // backward compatibility: row will be not clickable
            {
                $item = $notificationData;
                $disabled = true;

                if ( isset($item['url']) && strpos($item['url'], OW_URL_HOME) === 0 )
                {
                    $permalinkUri = str_replace(OW_URL_HOME, "", $item['url']);

                    $item['url'] = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute("base.desktop_version"), array(
                        "back-uri" => urlencode($permalinkUri)
                    ));
                }
            }

            $item['avatar'] = $avatars[$notification->id];

            if ( !empty($item['string']) && is_array($item['string']) )
            {
                $key = explode('+', $item['string']['key']);
                $vars = empty($item['string']['vars']) ? array() : $item['string']['vars'];
                $item['string'] = OW::getLanguage()->text($key[0], $key[1], $vars);
            }

            if ( !empty($item['string'])) {
                $item['string'] = UTIL_HtmlTag::stripTags($item['string']);
            }

            if ( !empty($item['contentImage']) )
            {
                $item['contentImage'] = is_string($item['contentImage'])
                    ? array( 'src' => $item['contentImage'] )
                    : $item['contentImage'];
            }
            else
            {
                $item['contentImage'] = null;
            }

            $item['viewed'] = (bool) $notification->viewed;
            $item['disabled'] = $disabled;
            $item['createTime'] = UTIL_DateTime::formatDate($notification->timeStamp);
            $item['hideUrl'] = OW::getRouter()->urlForRoute('notifications-hide', array("id" => $notification->id));
            $items[$notification->id] = $item;
        }

        return $items;
    }
}