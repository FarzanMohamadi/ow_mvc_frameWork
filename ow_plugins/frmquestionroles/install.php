<?php
/**
 * frmquestionroles
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmquestionroles
 * @since 1.0
 */


$authorization = OW::getAuthorization();
$groupName = 'frmquestionroles';
$authorization->addGroup($groupName);

$authorization->addAction($groupName, 'manage_question_roles', false, false);

OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmquestionroles_question_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleId` int(11) NOT NULL,
  `data` longtext,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
