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
class FRMCOMMENTPLUS_CLASS_EventHandler
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
        $eventManager->bind('base_delete_comment', array($service, 'deleteComment'));
        $eventManager->bind('feed.after_like_added', array($service, 'onAfterFeedLikeNotification'));
        $eventManager->bind('notifications.collect_actions', array($service, 'notificationActionAdd'));
        $eventManager->bind('frmcommentplus.add_reply_post_comment_button', array($this, 'addReplyPostCommentButton'));
        $eventManager->bind('frmcommentplus.check_reply_post_comment_request_params', array($service, 'checkReplyPostCommentRequestParams'));
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

        if (!isset($params['isReplyList'])) {
            return;
        }
        $isReplyList = $params['isReplyList'];

        if (!isset($params['parentAction'])) {
            return;
        }
        /* @var BASE_ContextAction */
        $parentAction = $params['parentAction'];

        $language = OW::getLanguage();
        $replyButton = OW::getUser()->isAuthenticated() && OW::getConfig()->getValue('frmcommentplus', 'enableReplyPostComment');

        if ($replyButton){
            $flagAction = new BASE_ContextAction();
            $flagAction->setLabel($language->text('base', 'contex_action_comment_reply_label'));
            $flagAction->setParentKey($parentAction->getKey());
            $replyId = 'reply-' . $value->getId();
            $flagAction->setId($replyId);
            if (!$isReplyList){
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
                    'replyUserDisplayNameId' => 'rudni'.$value->getReplyId(),
                    'replyCommentId' => $value->getReplyId(),
                    'replyName' => BOL_UserService::getInstance()->getDisplayName($value->getUserId()),
                    'userId' => $value->getUserId(),
                    'commentContent' => UTIL_String::truncate($value->getMessage(), 150, '...')
                );
            }

            $event->setData(array('actionParams' => $actionParams, 'flagAction' => $flagAction, 'replyId' => $replyId));
        }
    }

}