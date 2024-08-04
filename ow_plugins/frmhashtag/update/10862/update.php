<?php
$tblPrefix = OW_DB_PREFIX;

$dbo = Updater::getDbo();
$logger = Updater::getLogger();

try
{
    $table_name = OW_DB_PREFIX . 'frmhashtag_entity';
    if(!defined('BACKUP_TABLES_USING_TRIGGER') || BACKUP_TABLES_USING_TRIGGER == true) {
        $dropTableDontNeedBackupQuery = 'DROP TABLE IF EXISTS ' . FRMSecurityProvider::getTableBackupName($table_name);
        $dbo->query($dropTableDontNeedBackupQuery);

        $dropRemoveTriggerOfTableDontNeedBackupQuery = 'DROP TRIGGER IF EXISTS ' . FRMSecurityProvider::$removeTriggerNameBackupTable . $table_name;
        $dbo->query($dropRemoveTriggerOfTableDontNeedBackupQuery);

        $dropUpdateTriggerOfTableDontNeedBackupQuery = 'DROP TRIGGER IF EXISTS ' . FRMSecurityProvider::$updateTriggerNameBackupTable . $table_name;
        $dbo->query($dropUpdateTriggerOfTableDontNeedBackupQuery);
    }
}
catch (Exception $e)
{
    $logger->writeLog(OW_Log::ERROR, 'update_error', [json_encode($e)]);
}