<?php
$dbo = Updater::getDbo();
try{
    $dbo->query("ALTER TABLE `".OW_DB_PREFIX."mailbox_message` CONVERT TO CHARACTER SET utf8mb4;");
    $dbo->query("ALTER TABLE `". FRMSecurityProvider::getTableBackupName(OW_DB_PREFIX."mailbox_message")."` CONVERT TO CHARACTER SET utf8mb4;");
}catch (Exception $ex){}
try{
    $dbo->query("ALTER TABLE `".OW_DB_PREFIX."mailbox_message` MODIFY `text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $dbo->query("ALTER TABLE `". FRMSecurityProvider::getTableBackupName(OW_DB_PREFIX."mailbox_message")."` MODIFY `text` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
}catch (Exception $ex){}
