<?php

/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.story
 * @since 1.0
 */

try {
    OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "story_highlight_categories`;");
    OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'story_highlight_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `categoryTitle` varchar(128) NOT NULL,
  `categoryAvatar` int(11) NOT NULL,
  `createTime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

}catch (Exception $ex){
    OW::getLogger()->writeLog(OW_Log::ERROR, 'update_10815_story',
        ['actionType'=>OW_Log::UPDATE, 'enType'=>'plugin', 'enId'=> 'story', 'error'=>'error in mysql', 'exception'=>$ex]);
}
