<?php
/**
 * Created by PhpStorm.
 * User: ismail
 * Date: 2/17/18
 * Time: 2:06 PM
 */

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmrahyab_track` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` varchar(500),
  `mobile` varchar(15),
  `time` int(11),
  `smsId` varchar(100),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');