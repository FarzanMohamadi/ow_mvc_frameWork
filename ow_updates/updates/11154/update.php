<?php
try {
    $dbo = Updater::getDbo();
    $query = "
ALTER TABLE `" . OW_DB_PREFIX . "base_question`
ADD COLUMN `condition` TEXT;";
    $dbo->query($query);
}catch (Exception $ex){
    Updater::getLogger()->writeLog(OW_Log::ERROR, 'core_update_error_11154');
}