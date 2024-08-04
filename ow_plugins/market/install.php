<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */


try {
    OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "market_data`;");
    OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'market_data` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `userId` int(11) NOT NULL,
      `storeName` longtext NOT NULL,
      `createdAt` int(11) NOT NULL,
      `data` longtext DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

}catch (Exception $ex){
    OW::getLogger()->writeLog(OW_Log::ERROR, 'update_10101_market',
        ['actionType'=>OW_Log::UPDATE, 'enType'=>'plugin', 'enId'=> 'market', 'error'=>'error in mysql', 'exception'=>$ex]);
}
