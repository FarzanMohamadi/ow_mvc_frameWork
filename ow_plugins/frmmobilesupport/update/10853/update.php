<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
try {
    $tableName = OW_DB_PREFIX . 'frmmobilesupport_notifications';
    $q = 'DROP TABLE IF EXISTS `' . $tableName . '`';
    Updater::getDbo()->query($q);
    $tableName = OW_DB_PREFIX . 'frmmobilesupport_notifications';
    $q = 'DROP TABLE IF EXISTS `' . FRMSecurityProvider::getTableBackupName($tableName) . '`';
    Updater::getDbo()->query($q);
    $dropRemoveTriggerOfTableDontNeedBackupQuery = 'DROP TRIGGER IF EXISTS ' . FRMSecurityProvider::$removeTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($dropRemoveTriggerOfTableDontNeedBackupQuery);
    $dropUpdateTriggerOfTableDontNeedBackupQuery = 'DROP TRIGGER IF EXISTS ' . FRMSecurityProvider::$updateTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($dropUpdateTriggerOfTableDontNeedBackupQuery);
}catch (Exception $ex){}