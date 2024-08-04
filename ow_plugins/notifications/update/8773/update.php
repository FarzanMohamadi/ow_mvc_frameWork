<?php
$dbPrefix = OW_DB_PREFIX;

# first try-catch: it's fast and only takes a few seconds to perform
try {
    # create table
    $sql = "CREATE TABLE `{$dbPrefix}notifications_notification_data` (
      `id` int(11) NOT NULL auto_increment,
      `data` text,
      PRIMARY KEY  (`id`)
    ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
    Updater::getDbo()->query($sql);

    # send all data to new table
    $sql = "INSERT into `{$dbPrefix}notifications_notification_data` (`data`)
select distinct `data`
from `{$dbPrefix}notifications_notification`";
    Updater::getDbo()->query($sql);

    # add new column to old table
    $sql = "ALTER table `{$dbPrefix}notifications_notification` add column `dataId` INTEGER NULL";
    Updater::getDbo()->query($sql);
}catch (Exception $ex){
    Updater::getLogger()->writeLog(OW_Log::INFO,'notifications.update.1', ['log'=>json_encode($e)]);
}

# fill dataId for all rows (slow)
$sql = "
UPDATE `{$dbPrefix}notifications_notification` nt
JOIN (
	select distinct nt.`id` AS id1, dt.id AS id2
		from `{$dbPrefix}notifications_notification` nt 
	JOIN (`{$dbPrefix}notifications_notification_data` dt) ON dt.`data` = nt.`data`
) j ON j.id1 = nt.id
SET nt.dataId = j.id2
WHERE nt.dataId IS NULL
";
Updater::getDbo()->query($sql);

# remove data column from the old table
$sql = "ALTER TABLE `{$dbPrefix}notifications_notification` DROP COLUMN `data`";
Updater::getDbo()->query($sql);
