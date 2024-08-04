<?php
$query = "ALTER TABLE " . OW_DB_PREFIX . "newsfeed_action_set MODIFY COLUMN `id` BIGINT auto_increment AUTO_INCREMENT";
Updater::getDbo()->query($query);