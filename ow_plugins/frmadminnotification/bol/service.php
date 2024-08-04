<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadminnotification.bol
 * @since 1.0
 */
class FRMADMINNOTIFICATION_BOL_Service
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

    public function onUserRegistered(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['forEditProfile']) && $params['forEditProfile']==true){
            return;
        }
        $sendEmail = OW::getConfig()->getValue('frmadminnotification','registerNotification');
        $user = null;
        if(isset($params['userId'])  && $params['userId']!=null && isset($sendEmail)  && $sendEmail!=null){
            $user = BOL_UserService::getInstance()->findUserById($params['userId']);
            if($user!=null){

                $subject = OW::getLanguage()->text('frmadminnotification', 'registration_notice_subject');
                $profileUrl = OW::getRouter()->urlForRoute('base_user_profile', array('username' => $user->username));

                // check if user registered by sms
                $username = $user->username;
                $event = OW::getEventManager()->trigger(new OW_Event('frmadminnotification.send_info_after_user_registered', ['userId' => $user->getId()]));
                if(isset($event->getData()['username']))
                {
                    $username = $event->getData()['username'];
                }
                $message = OW::getLanguage()->text('frmadminnotification', 'registration_notice', array('username'=> $username, 'profile_url'=> $profileUrl, 'realname' => BOL_UserService::getInstance()->getDisplayName($user->getId()), 'join_date' => UTIL_DateTime::formatSimpleDate(time())));
                $this->sendMail($subject, $message, $this->getAdminMail(), OW::getConfig()->getValue('base', 'site_email'));
            }
        }
    }

    public function onPostTopicForumAdd(OW_Event $event){
        $params = $event->getParams();
        $sendEmail = OW::getConfig()->getValue('frmadminnotification','topicForumNotification');
        if(isset($params['topicId']) && $params['topicId']!=null && isset($params['userId']) && $params['userId']!=null && isset($sendEmail) && $sendEmail!=null){
            $user = BOL_UserService::getInstance()->findUserById($params['userId']);
            if($user!=null){
                $topicTitle = FORUM_BOL_ForumService::getInstance()->getTopicInfo($params['topicId'])['title'];
                $topicUrl = OW::getRouter()->urlForRoute('topic-default', array('topicId' => $params['topicId']));
                $subject = OW::getLanguage()->text('frmadminnotification', 'comment_topic_forum_add_subject');
                $message = OW::getLanguage()->text('frmadminnotification', 'comment_topic_forum_add_description', array('username'=> $user->username, 'topic_title' => $topicTitle, 'topicUrl' => $topicUrl));
                $this->sendMail($subject, $message, $this->getAdminMail(), OW::getConfig()->getValue('base', 'site_email'));
            }
        }
    }

    public function onTopicForumAdd(OW_Event $event){
        $params = $event->getParams();
        $sendEmail = OW::getConfig()->getValue('frmadminnotification','topicForumNotification');
        if(isset($params['topicTitle']) && $params['topicTitle']!=null && isset($params['topicUrl']) && $params['topicUrl']!=null && isset($params['userId']) && $params['userId']!=null && isset($sendEmail) && $sendEmail!=null){
            $user = BOL_UserService::getInstance()->findUserById($params['userId']);
            if($user!=null){
                $subject = OW::getLanguage()->text('frmadminnotification', 'topic_forum_add_subject');
                if(isset($params['edit']) && $params['edit']) {
                    $message = OW::getLanguage()->text('frmadminnotification', 'topic_forum_edit_description', array('username' => $user->username, 'topic_title' => $params['topicTitle'], 'topicUrl' => $params['topicUrl']));
                }else{
                    $message = OW::getLanguage()->text('frmadminnotification', 'topic_forum_add_description', array('username' => $user->username, 'topic_title' => $params['topicTitle'], 'topicUrl' => $params['topicUrl']));
                }
                $this->sendMail($subject, $message, $this->getAdminMail(), OW::getConfig()->getValue('base', 'site_email'));
            }
        }
    }


    public function onCommentAdd(OW_Event $event){
        $params = $event->getParams();
        $sendNewsCommentEmail = OW::getConfig()->getValue('frmadminnotification','newsCommentNotification');
        if(isset($params['entityType']) && $params['entityType']!=null && isset($params['entityId']) && $params['entityId']!=null && isset($params['userId']) && $params['userId']!=null){
            $user = BOL_UserService::getInstance()->findUserById($params['userId']);
            if($user!=null && $sendNewsCommentEmail!=null && isset($sendNewsCommentEmail) && $params['entityType']=='news-entry') {
                $news = EntryService::getInstance()->findById($params['entityId']);
                $newsTitle = $news->title;
                $newsUrl = EntryService::getInstance()->getEntryUrl($news);
                $subject = OW::getLanguage()->text('frmadminnotification', 'comment_news_add_subject');
                $message = OW::getLanguage()->text('frmadminnotification', 'comment_news_add_description', array('username' => $user->username, 'news_title' => $newsTitle, 'newsUrl' => $newsUrl));
                $this->sendMail($subject, $message, $this->getAdminMail(), OW::getConfig()->getValue('base', 'site_email'));
            }
        }
    }

    /***
     * Send an email
     * @param $subject
     * @param $message
     * @param $sendToEmail
     * @param $sendFromEmail
     */
    public function sendMail($subject, $message,$sendToEmail , $sendFromEmail){
        $mail = OW::getMailer()->createMail();
        $configs = OW::getConfig()->getValues('base');
        $mailStateEvent = new OW_Event('base_before_email_create', array('adminNotificationUser' => $configs['mail_smtp_user']));
        OW::getEventManager()->trigger($mailStateEvent);
        if(isset($mailStateEvent->getData()['adminNotificationUser'])){
            $sendFromEmail = $mailStateEvent->getData()['adminNotificationUser'];
        }
        $mail->addRecipientEmail($sendToEmail);
        $mail->setSender($sendFromEmail);
        $mail->setSenderSuffix(false);
        $mail->setSubject($subject);
        $mail->setTextContent($message);
        $mail->setHtmlContent($message);
        OW::getMailer()->send($mail);
    }

    /***
     * Return admin email
     * @return null|string
     */
    public function getAdminMail(){
        $adminMail = OW::getConfig()->getValue('frmadminnotification','emailSendTo');
        if(isset($adminMail) && $adminMail!=null){
            return $adminMail;
        }

        return OW::getConfig()->getValue('base', 'site_email');
    }
}