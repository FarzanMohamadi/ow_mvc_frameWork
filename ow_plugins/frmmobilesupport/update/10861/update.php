<?php
try
{
    $sql = "ALTER TABLE `".OW_DB_PREFIX."frmmobilesupport_app_version` ADD `message` varchar(400);";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }