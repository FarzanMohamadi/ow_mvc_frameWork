<?php

try {
    $name_table = OW_DB_PREFIX . 'mailbox_message';

    Updater::getDbo()->query("ALTER TABLE `" . $name_table ."` ADD COLUMN `costumeFeatures` JSON DEFAULT NULL");

}catch (Exception $ex){
    OW::getLogger()->writeLog(OW_Log::ERROR, 'update_11020_mailbox',
        ['actionType'=>OW_Log::UPDATE, 'enType'=>'plugin', 'enId'=> 'mailbox', 'error'=>'error in mysql', 'exception'=>$ex]);
}