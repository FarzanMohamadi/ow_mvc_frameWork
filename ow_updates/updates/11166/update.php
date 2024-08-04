<?php

FRMSecurityProvider::createBackupTables(new OW_Event('update_tables',
    ['update_tables' => [OW_DB_PREFIX . 'sessions']]));

$tableDontNeedBackup = OW_DB_PREFIX . 'sessions';
$dropTableDontNeedBackupQuery = 'DROP TABLE IF EXISTS ' . self::getTableBackupName($tableDontNeedBackup);
@OW::getDbo()->query($dropTableDontNeedBackupQuery);

$dropRemoveTriggerOfTableDontNeedBackupQuery = 'DROP TRIGGER IF EXISTS `' . self::$removeTriggerNameBackupTable . $tableDontNeedBackup.'`';
@OW::getDbo()->query($dropRemoveTriggerOfTableDontNeedBackupQuery);

$dropUpdateTriggerOfTableDontNeedBackupQuery = 'DROP TRIGGER IF EXISTS `' . self::$updateTriggerNameBackupTable . $tableDontNeedBackup.'`';
@OW::getDbo()->query($dropUpdateTriggerOfTableDontNeedBackupQuery);