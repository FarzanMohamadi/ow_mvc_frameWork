<?php
try {
    $tableName = OW_DB_PREFIX . 'frmmobilesupport_notifications';
    $q = 'DROP TABLE IF EXISTS `' . FRMSecurityProvider::getTableBackupName($tableName) . '`';
    Updater::getDbo()->query($q);
}
catch (Exception $ex) {}

try {
    $tableName = OW_DB_PREFIX . 'frmsecurityessentials_request_manager';
    $q = 'DROP TABLE IF EXISTS `' . FRMSecurityProvider::getTableBackupName($tableName) . '`';
    Updater::getDbo()->query($q);
}
catch (Exception $ex) {}

try {
    $tableName = OW_DB_PREFIX . 'base_db_cache';
    $q = 'DROP TABLE IF EXISTS `' . FRMSecurityProvider::getTableBackupName($tableName) . '`';
    Updater::getDbo()->query($q);
}
catch (Exception $ex) {}

try {
    $tableName = OW_DB_PREFIX . 'frmhashtag_tag';
    $q = 'DROP TABLE IF EXISTS `' . FRMSecurityProvider::getTableBackupName($tableName) . '`';
    Updater::getDbo()->query($q);
}
catch (Exception $ex) {}