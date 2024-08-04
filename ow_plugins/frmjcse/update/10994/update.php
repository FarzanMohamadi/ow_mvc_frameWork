<?php
$dbo = Updater::getDbo();
$query = "alter table `" . OW_DB_PREFIX . "frmjcse_article` add column `extra` TEXT";
$dbo->query($query);