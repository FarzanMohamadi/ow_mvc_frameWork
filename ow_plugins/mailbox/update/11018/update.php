<?php
$query = "ALTER TABLE `" . OW_DB_PREFIX . "mailbox_conversation` add column `muted` TINYINT(3) NOT NULL DEFAULT '0' AFTER `viewed`;";
try {
    Updater::getDbo()->query($query);
} catch (Exception $e) {
    Updater::getLogger()->writeLog(OW_LOG::ERROR, "update_mailbox_11018", ['message' => json_encode($e)]);
}

