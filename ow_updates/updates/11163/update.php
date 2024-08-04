<?php
try {
    $dbo = Updater::getDbo();
    $query = "
    CREATE TABLE `" . OW_DB_PREFIX . "sessions`
	(
		id varchar(32) NOT NULL,
		access int(10) unsigned,
		data text,
		PRIMARY KEY (id)
	) DEFAULT CHARSET=utf8;";
    $dbo->query($query);
} catch (Exception $ex) {
    Updater::getLogger()->writeLog(OW_Log::ERROR, 'core_update_error_11155');
}