<?php

$logger = Updater::getLogger();
try {
    $dbo = Updater::getDbo();

    $query = "ALTER TABLE `" . OW_DB_PREFIX . "base_comment`
    ADD COLUMN `replyId` int(11) DEFAULT NULL ";
    $dbo->query($query);

    $query = "ALTER TABLE `" . OW_DB_PREFIX . "base_comment`
    ADD COLUMN `replyUserId` int(11) DEFAULT NULL ";
    $dbo->query($query);

} catch (Exception $e)
{
    $logger->writeLog(OW_LOG::ERROR, "update_base_11182", ['message' => json_encode($e)]);
}