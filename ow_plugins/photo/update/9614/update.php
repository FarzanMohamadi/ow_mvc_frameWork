<?php
Updater::getLanguageService()->importPrefixFromZip(__DIR__ . DS . 'langs.zip', 'photo');

$sqls = array(
    'ALTER TABLE `' . OW_DB_PREFIX . 'photo_album` ADD INDEX (`entityType`, `entityId`);'
);

foreach ( $sqls as $sql )
{
    try
    {
        Updater::getDbo()->query($sql);
    }
    catch ( Exception $e )
    {
        Updater::getLogger()->addEntry(json_encode($e));
    }
}
