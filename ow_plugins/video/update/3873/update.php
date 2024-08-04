<?php
try
{
    $sql = "ALTER TABLE `".OW_DB_PREFIX."video_clip` ADD `privacy` varchar(50) NOT NULL default 'everybody';";

    Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }


Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'video');
