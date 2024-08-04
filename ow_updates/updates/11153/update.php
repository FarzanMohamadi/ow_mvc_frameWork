<?php
try {
    $dbo = Updater::getDbo();
    $query = "
ALTER TABLE `" . OW_DB_PREFIX . "base_user_disapprove`
ADD COLUMN `changeRequested` TINYINT(1) default 0;";
    $dbo->query($query);
}catch (Exception $ex){
}
try {
    $query = "
ALTER TABLE `" . OW_DB_PREFIX . "base_user_disapprove`
ADD COLUMN `notes` TEXT;";
    $dbo->query($query);
}catch (Exception $ex){
    Updater::getLogger()->writeLog(OW_Log::ERROR, 'core_update_error_11150');
}