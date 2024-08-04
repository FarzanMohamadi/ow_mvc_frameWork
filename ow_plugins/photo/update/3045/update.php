<?php
try
{
    $sql = "ALTER TABLE `".OW_DB_PREFIX."photo` ADD INDEX ( `addDatetime` );";
    
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ }

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'photo');