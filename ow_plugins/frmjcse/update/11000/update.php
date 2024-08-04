<?php
$dbo = Updater::getDbo();
$query = "alter table `" . OW_DB_PREFIX . "frmjcse_article` add column `views` INT";
$dbo->query($query);

$query = "UPDATE `" . OW_DB_PREFIX . "frmjcse_article` SET `views` = `dltimes`*34.3";
$dbo->query($query);