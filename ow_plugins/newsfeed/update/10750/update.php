<?php
$sqls = array(
    'ALTER TABLE `' . OW_DB_PREFIX . 'newsfeed_action_feed` ADD INDEX `feedId2` (`feedId`);'
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
