<?php
try
{
    $sql = "ALTER TABLE frmbckp_".OW_DB_PREFIX."frmmobilesupport_app_version MODIFY COLUMN `message` VARCHAR(400) AFTER `deprecated`;";
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }