<?php
$updateDir = dirname(__FILE__) . DS;
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'groups');

$query = "ALTER TABLE `" . OW_DB_PREFIX . "groups_invite` ADD `viewed` TINYINT( 1 ) NOT NULL DEFAULT '0', ADD INDEX ( `viewed` )";

try
{
    OW::getDbo()->query($query);
}
catch ( Exception $e )
{
    
}
