<?php
$query = "ALTER TABLE `" . OW_DB_PREFIX . "blogs_post` add column  `bundleId` varchar(128) default NULL";
try
{
    Updater::getDbo()->query($query);
}
catch (Exception $ex) {}
