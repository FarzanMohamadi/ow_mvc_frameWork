<?php
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'blogs');

$sql = "UPDATE `".OW_DB_PREFIX."base_language_value` SET `value`='View my blog posts' WHERE `keyId` = (SELECT `id` FROM `ow_base_language_key` WHERE `key` LIKE 'privacy_action_view_blog_posts' ) ";

$exArr = array();
try
{
    Updater::getDbo()->query($sql);
}
catch ( Exception $e ){ $exArr[] = $e; }