<?php
$dbPrefix = OW_DB_PREFIX;

# first try-catch: it's fast and only takes a few seconds to perform
try {
    # add new column to old table
    $sql = "ALTER table `{$dbPrefix}notifications_notification` add column `data` TEXT null";
    Updater::getDbo()->query($sql);
}catch (Exception $ex){
    Updater::getLogger()->writeLog(OW_Log::INFO,'notifications.update.1', ['log'=>json_encode($e)]);
}

# fill dataId for all rows (slow)
$sql = "
UPDATE `{$dbPrefix}notifications_notification` nt
JOIN (
	SELECT distinct nt2.`id` AS id1, dt.id AS id2, dt.`data` AS txt
		FROM `{$dbPrefix}notifications_notification` nt2 
	    JOIN (`{$dbPrefix}notifications_notification_data` dt) ON dt.id = nt2.dataId
) j ON j.id1 = nt.id
SET nt.`data` = j.`txt`
WHERE nt.`data` IS NULL
";
Updater::getDbo()->query($sql);

# remove data column from the old table
$sql = "ALTER TABLE `{$dbPrefix}notifications_notification` DROP COLUMN `dataId`";
Updater::getDbo()->query($sql);

# drop extra table
$sql = "DROP TABLE `{$dbPrefix}notifications_notification_data`;";
Updater::getDbo()->query($sql);