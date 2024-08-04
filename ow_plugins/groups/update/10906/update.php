<?php
$sql = "ALTER TABLE `".OW_DB_PREFIX."groups_group_user` ADD `last_seen_action` INT NULL";
Updater::getDbo()->query($sql);