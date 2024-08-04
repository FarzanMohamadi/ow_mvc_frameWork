<?php
$exArr = array();

$sql = "UPDATE `".OW_DB_PREFIX."base_comment_entity` 
    SET `pluginKey` = :pluginKey 
    WHERE `entityType` = :entityType";

try
{
    Updater::getDbo()->query($sql, array('pluginKey' => 'photo', 'entityType' => 'photo_comments'));
}
catch ( Exception $e ){ $exArr[] = $e; }

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'photo');
