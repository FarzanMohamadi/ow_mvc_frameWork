<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcommentplus.classes
 * @since 1.0
 */
class FRMCOMMENTPLUS_MCLASS_EventHandler
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

    public function init()
    {
        $service = FRMCOMMENTPLUS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('base_add_comment', array($service, 'frmcommentplus_afterComment_notification'));
        $eventManager->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
        OW::getEventManager()->bind('base_delete_comment', array($service, 'deleteComment'));
        $eventManager->bind('frmcommentplus.add_reply_post_comment_button', array($this, 'addReplyPostCommentButton'));
        $eventManager->bind('frmcommentplus.check_reply_post_comment_request_params', array($service, 'checkReplyPostCommentRequestParams'));
    }

    public function onNotificationRender( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] != 'base'|| ($params['entityType'] != 'base_profile_wall'))
        {
            return;
        }

        $data = $params['data'];

        if ( !isset($data['avatar']['urlInfo']['vars']['username']) )
        {
            return;
        }

        $userService = BOL_UserService::getInstance();
        $user = $userService->findByUsername($data['avatar']['urlInfo']['vars']['username']);
        if ( !$user )
        {
            return;
        }
        $e->setData($data);
    }

    public function addReplyPostCommentButton(OW_Event $event) {
        $params = $event->getParams();
        if (!isset($params['value'])) {
            return;
        }
        /* @var $value BOL_Comment */
        $value = $params['value'];

        if (!isset($params['replyCommentId'])) {
            return;
        }
        $replyCommentId = $params['replyCommentId'];


        $replyButton = OW::getUser()->isAuthenticated() && OW::getConfig()->getValue('frmcommentplus', 'enableReplyPostComment');
        if ($replyButton) {
            $replyId = 'reply-' . $value->getId();
            $replyArray =  array(
                'key' => 'reply',
                'label' => OW::getLanguage()->text('base', 'contex_action_comment_reply_label'),
                'order' => 2,
                'class' => null,
                'id' => $replyId,
            );

            if ($replyCommentId){
                $actionParams = array(
                    'replyUserDisplayNameId' => 'rudni'.$replyCommentId,
                    'replyCommentId' => $replyCommentId,
                    'replyName' => BOL_UserService::getInstance()->getDisplayName($value->getUserId()),
                    'userId' => $value->getUserId(),
                    'commentContent' => UTIL_String::truncate($value->getMessage(), 150, '...')
                );
            }
            else{
                $actionParams = array(
                    'replyUserDisplayNameId' => 'rudni'.$value->getId(),
                    'replyCommentId' => $value->getId(),
                    'replyName' => '',
                    'commentContent' => UTIL_String::truncate($value->getMessage(), 150, '...')
                );
            }

            $event->setData(array('actionParams' => $actionParams, 'replyArray' => $replyArray, 'replyId' => $replyId));

        }
    }


}