<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/27/2017
 * Time: 9:15 AM
 */
OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmfarapayamak_track`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmfarapayamak_track` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` varchar(500),
  `mobile` varchar(15),
  `time` int(11),
  `smsId` varchar(100),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
