<?php
$query = "ALTER TABLE `" . OW_DB_PREFIX . "mailbox_message` add column  `isForwarded` int(10) NOT NULL default '0'";
try
{
    Updater::getDbo()->query($query);
}
catch (Exception $ex) {}

