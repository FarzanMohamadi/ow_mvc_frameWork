<?php
$sql = " DELETE FROM `" . OW_DB_PREFIX . "frmgroupsplus_group_managers` WHERE `userId` NOT IN (SELECT `id` FROM `" . OW_DB_PREFIX . "base_user`)";
Updater::getDbo()->query($sql);

