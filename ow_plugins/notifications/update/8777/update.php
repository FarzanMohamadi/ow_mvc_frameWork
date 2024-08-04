<?php
$sqls = array(
    'ALTER TABLE `' . OW_DB_PREFIX . 'notifications_notification` ADD INDEX `viewed` (`viewed`);'
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
