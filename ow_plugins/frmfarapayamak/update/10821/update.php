<?php
/**
 * Created by PhpStorm.
 * User: ismail
 * Date: 1/3/18
 * Time: 2:58 PM
 */

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmfarapayamak_track` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` varchar(500),
  `mobile` varchar(15),
  `time` int(11),
  `smsId` varchar(100),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');