<?php
class MAILBOX_Cron extends OW_Cron
{
    const UPLOAD_FILES_REMOVE_TIMEOUT = 86400; // 1 day

    public function __construct()
    {
        parent::__construct();

        $this->addJob('clearMessages', 20);

        $this->addJob('resetAllUsersLastData', 15);
        $this->addJob('deleteAttachmentFiles', 1440); //1 day
    }

    public function run()
    {
        //ignore
    }

    public function clearMessages()
    {
        $deletedMessages = MAILBOX_BOL_DeletedMessageDao::getInstance()->findAll();
        foreach ($deletedMessages as $deletedMessage){
            if(time() > $deletedMessage->time + 60)
                MAILBOX_BOL_DeletedMessageDao::getInstance()->deleteById($deletedMessage->id);
        }
        $changedMessages = MAILBOX_BOL_MessageDao::getInstance()->getChangedData();
        foreach ($changedMessages as $changedMessage){
            $changedMessage->changed = 2;
            MAILBOX_BOL_MessageDao::getInstance()->save($changedMessage);
        }
    }

    public function resetAllUsersLastData()
    {
        $sql = "SELECT COUNT(*) FROM `".MAILBOX_BOL_UserLastDataDao::getInstance()->getTableName()."` AS `uld`
LEFT JOIN `".BOL_UserOnlineDao::getInstance()->getTableName()."` AS uo ON uo.userId = uld.userId
WHERE uo.id IS NULL OR uo.activityStamp < :expireTimestamp";

        $usersOfflineButOnline = OW::getDbo()->queryForColumn($sql, array(
            'expireTimestamp' => BOL_UserService::getInstance()->getOnlineUserExpirationTimestamp(),
        ));
        if ($usersOfflineButOnline > 0)
        {
            MAILBOX_BOL_ConversationService::getInstance()->resetAllUsersLastData();
        }
    }
    
    public function deleteAttachmentFiles()
    {
        MAILBOX_BOL_ConversationService::getInstance()->deleteAttachmentFiles();
    }
}