<?php
$exArr = array();

$sql = "UPDATE `".OW_DB_PREFIX."base_comment_entity` 
    SET `pluginKey` = :pluginKey 
    WHERE `entityType` = :entityType";

try
{
    Updater::getDbo()->query($sql, array('pluginKey' => 'blogs', 'entityType' => 'blog-post'));
}
catch ( Exception $e ){ $exArr[] = $e; }
    

