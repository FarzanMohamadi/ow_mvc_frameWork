<?php
/**
 * FRM Challenge
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmchallenge
 * @since 1.0
 */

OW::getConfig()->saveConfig('frmchallenge', 'solitary_question_count', 5);
OW::getConfig()->saveConfig('frmchallenge', 'solitary_answer_time', 60 * 60 * 24);
OW::getConfig()->saveConfig('frmchallenge', 'universal_question_count', 10);
OW::getConfig()->saveConfig('frmchallenge', 'universal_answer_time', 60 * 60 * 24 * 10);

try {
    $authorization = OW::getAuthorization();
    $groupName = 'frmchallenge';
    $authorization->addGroup($groupName);
    $authorization->addAction($groupName, 'add_universal_challenge');
    $authorization->addAction($groupName, 'add_solitary_challenge');

}catch(Exception $e){

}

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmchallenge_challenge`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmchallenge_challenge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` longtext,
  `description` longtext,
  `sponsor` longtext,
  `prize` longtext,
  `minPoint` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `winPoint` int(11) NOT NULL,
  `losePoint` int(11) NOT NULL,
  `equalPoint` int(11) NOT NULL,
  `finishDate` int(11),
  `status` int(11) NOT NULL,
  `createDate` int(11) NOT NULL,
  `categories` longtext,
  `cancelerId` int(11),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmchallenge_user`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmchallenge_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `point` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmchallenge_question`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmchallenge_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` longtext,
  `categoryId` int(11),
  `point` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmchallenge_answer`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmchallenge_answer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionId` int(11) NOT NULL,
  `title` longtext NOT NULL,
  `correct` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmchallenge_booklet`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmchallenge_booklet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionId` int(11) NOT NULL,
  `challengeId` int(11) NOT NULL,
  `userIdSeen` int(1) NOT NULL,
  `opponentIdSeen` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmchallenge_challenge_solitary`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmchallenge_challenge_solitary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `opponentId` int(11),
  `challengeId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmchallenge_challenge_universal`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmchallenge_challenge_universal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `challengeId` int(11) NOT NULL,
  `winNum` int(11),
  `questionsNumber` int(11),
  `startTime` int(11),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmchallenge_challenge_category`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmchallenge_challenge_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` longtext,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

OW::getDbo()->query("
DROP TABLE IF EXISTS  `" . OW_DB_PREFIX . "frmchallenge_challenge_user_answer`;");
OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmchallenge_challenge_user_answer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `challengeId` int(11) NOT NULL,
  `questionId` int(11) NOT NULL,
  `answerId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
