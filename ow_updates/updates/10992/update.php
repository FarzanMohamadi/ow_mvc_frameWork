<?php
try {
    $tableName = OW_DB_PREFIX . 'frmmobilesupport_notifications';
    $q = 'DROP TRIGGER IF EXISTS `' . FRMSecurityProvider::$removeTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($q);
    $q = 'DROP TRIGGER IF EXISTS `' . FRMSecurityProvider::$updateTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($q);
}
catch (Exception $ex) {}

try {
    $tableName = OW_DB_PREFIX . 'frmsecurityessentials_request_manager';
    $q = 'DROP TRIGGER IF EXISTS `' . FRMSecurityProvider::$removeTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($q);
    $q = 'DROP TRIGGER IF EXISTS `' . FRMSecurityProvider::$updateTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($q);
}
catch (Exception $ex) {}

try {
    $tableName = OW_DB_PREFIX . 'base_db_cache';
    $q = 'DROP TRIGGER IF EXISTS `' . FRMSecurityProvider::$removeTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($q);
    $q = 'DROP TRIGGER IF EXISTS `' . FRMSecurityProvider::$updateTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($q);
}
catch (Exception $ex) {}

try {
    $tableName = OW_DB_PREFIX . 'frmhashtag_tag';
    $q = 'DROP TRIGGER IF EXISTS `' . FRMSecurityProvider::$removeTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($q);
    $q = 'DROP TRIGGER IF EXISTS `' . FRMSecurityProvider::$updateTriggerNameBackupTable . $tableName;
    Updater::getDbo()->query($q);
}
catch (Exception $ex) {}