<?php

$dbo = Updater::getDbo();
$query = "alter table `" . OW_DB_PREFIX . "frmjcse_author` add column `email` VARCHAR( 50 )";
$dbo->query($query);

$query = "alter table `" . OW_DB_PREFIX . "frmjcse_author` add column `affliation` VARCHAR( 150 )";
$dbo->query($query);

