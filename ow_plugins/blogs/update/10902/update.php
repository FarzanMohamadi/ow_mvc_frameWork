<?php
$query = "ALTER TABLE `" . FRMSecurityProvider::$prefixBackuplabel . OW_DB_PREFIX . "blogs_post` add column  `bundleId` varchar(128) default NULL AFTER `privacy`";
try
{
    Updater::getDbo()->query($query);
}
catch (Exception $ex) {}
