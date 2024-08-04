<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */


try {
    OW::getDbo()->query("
    DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "passwordsecurity`;");
    OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'passwordsecurity` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `userId` int(11) NOT NULL,
      `isActive` TINYINT(1) NOT NULL,
      `password` varchar(64) NOT NULL,
      `lastUpdate` int(11) NOT NULL,
      `sectionsList` JSON DEFAULT NULL,
      PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

} catch (Exception $e) {
    
    
    OW::getLogger()->writeLog(OW_Log::ERROR, 'install_plugin_passwordsecurity',
        ['actionType'=>OW_Log::CREATE, 'enType'=>'plugin', 'enId'=> 'passwordsecurity', 'error'=>'error in mysql', 'exception'=>$e]);
    
}
