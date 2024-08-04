<?php
$sql = "ALTER TABLE `".OW_DB_PREFIX."video_clip` ADD `thumbUrl`
    VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `privacy`;";

try
{
    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    $exArr[] = $e;
}

$sql = "ALTER TABLE `".OW_DB_PREFIX."video_clip` ADD `thumbCheckStamp` INT NULL DEFAULT NULL AFTER `thumbUrl`;";

try
{
    Updater::getDbo()->query($sql);
}
catch ( Exception $e )
{
    $exArr[] = $e;
}