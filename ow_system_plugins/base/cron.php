<?php
class BASE_Cron extends OW_Cron
{
    const EMAIL_VARIFY_CODE_REMOVE_TIMEOUT = 432000; // 5 days

    // minutes


    public function __construct()
    {
        parent::__construct();

        $this->addJob('dbCacheProcess', 1);
        $this->addJob('mailQueueProcess', 1);

        $this->addJob('deleteExpiredOnlineUserProcess', 1);

        $this->addJob('checkPluginUpdates', 60 * 24);
        $this->addJob('deleteExpiredPasswordResetCodes', 10);
        $this->addJob('rmTempAttachments', 60 * 24);
        $this->addJob('rmTempAvatars', 60);
        $this->addJob('deleteExpiredCache', 60 * 24);
        $this->addJob('dropLogFile', 60);
        $this->addJob('clearMySqlSearchIndex', 60 * 24);
        $this->addJob('expireSearchResultList', 1);
        $this->addJob('generateSitemap', 1);
        $this->addJob('removeExpiredLoginCookies', 60);

        $this->addJob('deleteExpiredEmailVerifies', 60 * 24);
        $this->addJob('deleteOldEmailsBackupTable', 60 * 24);

        $this->addJob('deleteUnusedTags', 60 * 24);
    }

    /**
     * Generate sitemap
     */
    public function generateSitemap()
    {
        $service = BOL_SeoService::getInstance();

        // is it possible to start sitemap generating?
        if ( $service->isSitemapReadyForNextBuild() )
        {
            $service->generateSitemap();
        }
    }

    public function run()
    {
        BOL_UserService::getInstance()->cronSendWellcomeLetter();
    }

    public function dbCacheProcess()
    {
        // Delete expired db cache entry
        BOL_DbCacheService::getInstance()->deleteExpiredList();
    }

    public function mailQueueProcess()
    {
        // Send mails from mail queue
        BOL_MailService::getInstance()->processQueue();
    }

    public function deleteExpiredOnlineUserProcess()
    {
        BOL_UserService::getInstance()->deleteExpiredOnlineUsers();
    }

    public function expireSearchResultList()
    {
        BOL_SearchService::getInstance()->deleteExpireSearchResult();
    }

    public function clearMySqlSearchIndex()
    {
        $mysqlSearchStorage = new BASE_CLASS_MysqlSearchStorage();
        $mysqlSearchStorage->realDeleteEntities();
    }

    public function checkPluginUpdates()
    {
        BOL_StorageService::getInstance()->checkUpdates();
    }

    public function deleteExpiredPasswordResetCodes()
    {
        BOL_UserService::getInstance()->deleteExpiredResetPasswordCodes();
    }

    public function rmTempAttachments()
    {
        BOL_AttachmentService::getInstance()->deleteExpiredTempImages();
    }

    public function rmTempAvatars()
    {
        BOL_AvatarService::getInstance()->deleteTempAvatars();
    }

    public function deleteExpiredCache()
    {
        OW::getCacheManager()->clean(array(), OW_CacheManager::CLEAN_OLD);
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $path
     * @param $filename0
     */
    public function checkFileSizeAndZip($path, $filename0) {
        $logFilePath = $path . $filename0;
        if ( OW::getStorage()->fileExists($logFilePath) )
        {
            $logFileSize = filesize($logFilePath);
            $logFileSizeInMB = (int)($logFileSize / 1024 / 1024);
            $maxFileSizeInMB = (int) OW::getConfig()->getValue('base', 'log_file_max_size_mb');
            if ( $logFileSize !== false && $logFileSizeInMB >= $maxFileSizeInMB )
            {
                $timeStr = date('Y-m-d-h-i', time());
                $filename = $logFilePath . '_' . $timeStr;
                OW::getStorage()->renameFile($logFilePath, $filename);

                //Zip file
                $zip = new ZipArchive();
                if ($zip->open($filename.'.zip', ZipArchive::CREATE)==TRUE) {
                    $zip->addFile($filename,$filename0 . '_' . $timeStr);
                    echo "Zip log file, status:" . $zip->status . "\n";
                    $zip->close();
                    OW::getStorage()->removeFile($filename);
                }

                // E-mail moderators
                $mails = array();
                $moderators = BOL_AuthorizationService::getInstance()->getModeratorList();
                foreach ( $moderators as $moderator ) {
                    $user = BOL_UserService::getInstance()->findUserById($moderator->userId);
                    if(!BOL_UserService::getInstance()->isAdmin($user->getId())){
                        continue;
                    }
                    $siteName = OW::getConfig()->getValue('base', 'site_name');
                    $mail = OW::getMailer()->createMail();
                    $mail->addRecipientEmail($user->email);
                    $msg = 'Size of log file has become more than ' . $logFileSizeInMB . ' MB. File address: '.$filename.'.zip';
                    $mail->setHtmlContent($msg);
                    $mail->setTextContent($msg);
                    if ( $logFileSizeInMB >= 2 * $maxFileSizeInMB ) {
                        // if it became too much in a short time
                        $mail->setSubject($siteName . ': Log file zipped - Size of log file has grown rapidly in a short time!');
                    }else{
                        // with the zip file attached
                        $mail->setSubject($siteName . ': Log file zipped!');
                    }
                    $mails[] = $mail;
                }
                OW::getMailer()->addListToQueue($mails);
            }
        }
    }

    public function dropLogFile()
    {
        $this->checkFileSizeAndZip(OW_DIR_LOG, 'log.log');
        $this->checkFileSizeAndZip(OW_DIR_LOG, 'rabbitmq.log');
        $this->checkFileSizeAndZip(OW_DIR_LOG, 'cron.log');
        $this->checkFileSizeAndZip(OW_DIR_LOG, 'socket_server.log');
    }

    public function removeExpiredLoginCookies(){
        BOL_UserService::getInstance()->removeExpiredLoginCookies();
    }

    public function deleteExpiredEmailVerifies()
    {
        //clean email varify code table
        BOL_EmailVerifyService::getInstance()->deleteByCreatedStamp(time() - self::EMAIL_VARIFY_CODE_REMOVE_TIMEOUT);
    }

    public function deleteOldEmailsBackupTable(){
        $prefix = 'frmbckp_' .OW_DB_PREFIX;
        $time = time() - 6 * 31 * 24 * 60 * 60;
        $query  = "DELETE FROM {$prefix}base_mail WHERE backup_timestamp<{$time}";
        OW::getDbo()->query($query);
    }

    public function deleteUnusedTags()
    {
        BOL_TagService::getInstance()->deleteUnusedBaseTags();
    }
}
