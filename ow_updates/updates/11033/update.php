<?php
try
{
    $logger = Updater::getLogger();
    $dbo = Updater::getDbo();

    $query = "ALTER TABLE `".OW_DB_PREFIX."base_media_panel_file` MODIFY `plugin` VARCHAR(255) NOT NULL";
    $dbo->query($query);

}
catch (Exception $e)
{
    $logger->addEntry(json_encode($e));
}

