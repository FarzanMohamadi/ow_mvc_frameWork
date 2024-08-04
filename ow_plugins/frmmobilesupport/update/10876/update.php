<?php
$sqls = array(
    'ALTER TABLE `' . OW_DB_PREFIX . 'frmmobilesupport_device` ADD INDEX userId (`userId`);',
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
