<?php
$sqls = array(
    'ALTER TABLE `' . OW_DB_PREFIX . 'groups_group_user` ADD INDEX `userId` (`userId`);',
    'ALTER TABLE `' . OW_DB_PREFIX . 'groups_group_user` ADD INDEX `groupId2` (`groupId`);',
    'ALTER TABLE `' . OW_DB_PREFIX . 'groups_group_user` ADD INDEX `last_seen_action` (`last_seen_action`);'
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
