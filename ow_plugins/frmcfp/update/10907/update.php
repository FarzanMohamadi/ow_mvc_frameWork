<?php
$query = "ALTER TABLE `" . OW_DB_PREFIX . "frmcfp_item` ADD COLUMN `fileDisabled` TINYINT(1) NOT NULL DEFAULT 0";
OW::getDbo()->query($query);

$query = "ALTER TABLE `" . OW_DB_PREFIX . "frmcfp_item` ADD COLUMN `fileNote` TEXT NOT NULL";
OW::getDbo()->query($query);

$query = "UPDATE `" . OW_DB_PREFIX . "frmcfp_item` SET `fileNote` = ''";
OW::getDbo()->query($query);
