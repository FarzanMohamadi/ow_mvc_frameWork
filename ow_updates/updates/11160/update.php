<?php
try {
    $dbo = Updater::getDbo();
    $query = "ALTER TABLE `" . OW_DB_PREFIX . "base_question`
            ADD COLUMN `editable` TINYINT(1) NOT NULL DEFAULT '1'
            AFTER `removable`";
    $dbo->query($query);

    $query ="UPDATE `" . OW_DB_PREFIX . "base_question`
                    SET `editable` = 0
                    WHERE `name` = 'username' 
                        OR `name` = 'password'
                        OR `name` = 'email'
                        OR `name` = 'joinStamp';";
    $dbo->query($query);
}catch (Exception $ex){
    Updater::getLogger()->writeLog(OW_Log::ERROR, 'core_update_error_11160');
}





