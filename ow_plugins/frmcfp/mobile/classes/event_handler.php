<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcfp.classes
 * @since 1.0
 */
class FRMCFP_MCLASS_EventHandler
{
    /**
     * Class instance
     *
     * @var FRMCFP_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FRMCFP_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function init()
    {
        $em = OW::getEventManager();
        $em->bind('feed.on_item_render', array($this, 'onFeedItemRenderDisableActions'));
        $em->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
        $em->bind('base.mobile_top_menu_add_options', array($this, 'onMobileTopMenuAddLink'));
        $em->bind(FRMCFP_BOL_Service::EVENT_DELETE_FILES, array(FRMCFP_BOL_Service::getInstance(), 'deleteFiles'));
        $em->bind(FRMCFP_BOL_Service::EVENT_ADD_FILE_WIDGET, array(FRMCFP_BOL_Service::getInstance(), 'addFileWidget'));

        FRMCFP_CLASS_InvitationHandler::getInstance()->init();
    }

    public function onFeedItemRenderDisableActions( OW_Event $event )
    {

        $params = $event->getParams();
        if ( $params["action"]["entityType"] != 'frmcfp' )
        {
            return;
        }

        $data = $event->getData();

        $data["disabled"] = false;

        $event->setData($data);
    }
    public function onMobileTopMenuAddLink( BASE_CLASS_EventCollector $event )
    {
        if ( OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('frmcfp', 'add_event')){
            $id = FRMSecurityProvider::generateUniqueId('event_add');
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('frmcfp', 'add_event');
            OW::getDocument()->addScriptDeclaration(
                UTIL_JsGenerator::composeJsString(
                    ';$("#" + {$btn}).on("click", function()
                {
                    OWM.showContent();
                    OWM.authorizationLimitedFloatbox({$msg});
                });',
                    array(
                        'btn' => $id,
                        'msg' => $status['msg'],
                    )
                )
            );
            $event->add(array(
                'prefix' => 'event',
                'key' => 'event_mobile',
                'id' => $id,
                'url' => OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('frmcfp.add'))
            ));
        }
    }

    public function onNotificationRender( OW_Event $e )
    {
        $params = $e->getParams();
        if ( $params['pluginKey'] != 'frmcfp' || !in_array($params['entityType'], ['cfp', 'frmcfp']) )
        {
            return;
        }

        $userId = $params["data"]["avatar"]["userId"];

        $userService = BOL_UserService::getInstance();
        $commentId = $params['entityId'];
        $comment = BOL_CommentService::getInstance()->findComment($commentId);
        if ( !$comment )
        {
            return;
        }
        $commEntity = BOL_CommentService::getInstance()->findCommentEntityById($comment->commentEntityId);
        if ( !$commEntity )
        {
            return;
        }
        $eventDto = FRMCFP_BOL_Service::getInstance()->findEvent($commEntity->entityId);
        $eventUrl = OW::getRouter()->urlForRoute('frmcfp.view', array('eventId' => $eventDto->getId()));
        if (OW::getUser()->getId() != $eventDto->userId) {
            $data = $params['data'];
            $e->setData($data);
        } else {
            $langVars = array(
                'userName' => $userService->getDisplayName($userId),
                'userUrl' => $userService->getUserUrl($userId),
                'url' => $eventUrl,
                'title' => strip_tags($eventDto->getTitle())
            );

            $data['string'] = array('key' => 'frmcfp+email_notification_comment', 'vars' => $langVars);

            //Notification on click logic is set here
            $event = new OW_Event('mobile.notification.data.received', array('pluginKey' => $params['pluginKey'],
                'entityType' => $params['entityType'],
                'data' => $data));
            OW::getEventManager()->trigger($event);
            if (isset($event->getData()['url'])) {
                $data['url'] = $event->getData()['url'];
            }

            $e->setData($data);
        }
    }
}