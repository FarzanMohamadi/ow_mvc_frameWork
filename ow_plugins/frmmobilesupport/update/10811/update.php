<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 7/13/2017
 */

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmmobilesupport_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` longtext NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');