<?php
$query = "ALTER TABLE `" . OW_DB_PREFIX . "mailbox_attachment` add column `thumbName` VARCHAR(255) DEFAULT NULL AFTER `fileSize`;";

try {
    Updater::getDbo()->query($query);
} catch (Exception $e) {
    Updater::getLogger()->writeLog(OW_LOG::ERROR, "update_mailbox_11025", ['message' => json_encode($e)]);
}