<?php
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmsecurityessentials_request_manager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `senderId` int(11) NOT NULL,
  `receiverId` int(11) NOT NULL,
  `code` varchar(150) NOT NULL,
  `activityType` varchar(100) NOT NULL,
  `expirationTimeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `senderId` (`senderId`),
  KEY `receiverId` (`receiverId`)
) DEFAULT CHARSET=utf8 ;');

//remove swf from valid extensions
FRMSecurityProvider::sanitizeValidExtList();