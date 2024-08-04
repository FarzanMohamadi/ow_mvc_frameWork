<?php
$dbo = Updater::getDbo();
try{
    $dbo->query("ALTER TABLE `".OW_DB_PREFIX."mailbox_message` CONVERT TO CHARACTER SET utf8mb4;");
    $dbo->query("ALTER TABLE `". FRMSecurityProvider::getTableBackupName(OW_DB_PREFIX."mailbox_message")."` CONVERT TO CHARACTER SET utf8mb4;");
}catch (Exception $ex){}
try{
    $dbo->query("ALTER TABLE `".OW_DB_PREFIX."mailbox_message` MODIFY `text` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $dbo->query("ALTER TABLE `". FRMSecurityProvider::getTableBackupName(OW_DB_PREFIX."mailbox_message")."` MODIFY `text` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
}catch (Exception $ex){}

$all_messages = MAILBOX_BOL_MessageDao::getInstance()->findAll();
foreach ($all_messages as $message){
    $text = $message->text;
    if(empty($text))
        continue;
    if(strpos($text, '<json>')===0) {
        $text = json_decode(substr($text, 6));
        $message->text = $text;
        MAILBOX_BOL_MessageDao::getInstance()->save($message);
    }
}
