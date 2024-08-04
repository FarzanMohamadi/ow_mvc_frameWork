<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 2/25/18
 * Time: 2:50 PM
 */

class FRMQUESTIONS_MCLASS_EventHandler
{

    public function __construct()
    {
    }
    public function init()
    {
        $eventManager = OW::getEventManager();
        $service = FRMQUESTIONS_BOL_Service::getInstance();
        $eventManager->bind('notifications.collect_actions', array($service, 'onNotifyActions'));
        $eventManager->bind(FRMEventManager::ON_AFTER_UPDATE_STATUS_FORM_RENDERER, array($service, 'addButtonToNewsfeedMobile'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_UPDATE_STATUS_FORM_RENDERER, array($service, 'addInputFieldsToNewsfeed'));
        $eventManager->bind('feed.after_activity', array($service, 'feedAdded'));
        $eventManager->bind(FRMEventManager::ON_FEED_ITEM_RENDERER, array($service, 'onFeedRenderMobile'));
        $eventManager->bind('newsfeed.generic_item_render', array($service, 'genericItemRenderMobile'));
        $eventManager->bind('feed.before_action_delete', array($service, 'deleteAction'));
        $eventManager->bind('feed.on_entity_action', array($service, 'onEntityAction'));
        $eventManager->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
        $eventManager->bind('base.on.before.forward.status.create', array($service, 'onForward'));
        OW::getEventManager()->bind('notification.get_edited_data', array($service, 'getEditedDataNotification'));
        OW::getEventManager()->bind('feed.on_item_render', array($service, "onNewsfeedItemRender"));
        $eventManager->bind('on.status.update.check.data', array($service, 'onStatusUpdateCheckData'));
    }

    public function onNotificationRender( OW_Event $e )
    {
        $params = $e->getParams();
        if ( $params['pluginKey'] != 'frmquestions'|| ($params['entityType'] != 'question_answer' && $params['entityType'] !='question_option'))
        {
            return;
        }

        $data = $params['data'];

        if ( !isset($data['avatar']['urlInfo']['vars']['username']) )
        {
            return;
        }

        //Notification on click logic is set here
        $event = new OW_Event('mobile.notification.data.received', array('pluginKey' => $params['pluginKey'],
            'entityType' => $params['entityType'],
            'data' => $data));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['url'])){
            $data['url']=$event->getData()['url'];
        }

        $userService = BOL_UserService::getInstance();
        $user = $userService->findByUsername($data['avatar']['urlInfo']['vars']['username']);
        if ( !$user )
        {
            return;
        }
        $questionName = OW::getConfig()->getValue('base', 'display_name_question');
        foreach ( array('string', 'conten') as $langProperty ) {
            if (!empty($data[$langProperty]) && is_array($data[$langProperty])) {
                if ($questionName == "username") {
                    $userName = BOL_UserService::getInstance()->getUserName($data['avatar']['userId']);
                } else {
                    $userName = BOL_UserService::getInstance()->getDisplayName($data['avatar']['userId']);
                }
                if ($userName) {
                    $data['string']['vars']['userName'] = $userName;
                }
            }
        }

        $e->setData($data);
    }
}
