<?php

try {
    $story_table = OW_DB_PREFIX . 'story';

    Updater::getDbo()->query("ALTER TABLE `" . $story_table ."` ADD COLUMN `thumbnailId` int(11) DEFAULT NULL");

}catch (Exception $ex){
    OW::getLogger()->writeLog(OW_Log::ERROR, 'update_10817_story',
        ['actionType'=>OW_Log::UPDATE, 'enType'=>'plugin', 'enId'=> 'story', 'error'=>'error in mysql', 'exception'=>$ex]);
}
