<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

$sql = "ALTER TABLE `".OW_DB_PREFIX."notifications_notification` 
   DROP INDEX entityType, 
   ADD UNIQUE KEY `entityType` (`userId`, `entityId`, `entityType`)";

Updater::getDbo()->query($sql);