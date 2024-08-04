<?php
$authorization = OW::getAuthorization();
$groupName = 'frmjcse';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'edit');


$sql = "CREATE TABLE `" . OW_DB_PREFIX . "frmjcse_issue` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `volume` INT(11) NOT NULL,
    `no` INT(11) NOT NULL,
    `posterfile` VARCHAR(200),
    `file` VARCHAR(200),
    `ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
)
CHARSET=utf8 AUTO_INCREMENT=1";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE `" . OW_DB_PREFIX . "frmjcse_article` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `abstract` TEXT,
    `citation` TEXT,
    `file` VARCHAR(200),
    `active` INT(1) NOT NULL DEFAULT 1,
    `issueid` INT(11),
    `startPage` INT(5),
    `endPage` INT(5),
    `ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `dltimes` INT(11) NOT NULL DEFAULT 0,
    `extra` TEXT,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`issueid`)
        REFERENCES `". OW_DB_PREFIX ."frmjcse_issue`(`id`)
        ON DELETE SET NULL
)
CHARSET=utf8 AUTO_INCREMENT=1";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE `" . OW_DB_PREFIX . "frmjcse_keyword` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL,
    `articleid` INT(11),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`articleid`)
        REFERENCES `". OW_DB_PREFIX ."frmjcse_article`(`id`)
        ON DELETE CASCADE
)
CHARSET=utf8 AUTO_INCREMENT=1";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE `" . OW_DB_PREFIX . "frmjcse_author` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL,
	`email` VARCHAR(50) NULL DEFAULT NULL,
	`affliation` VARCHAR(300) NULL DEFAULT NULL,
    `articleid` INT(11),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`articleid`)
        REFERENCES `". OW_DB_PREFIX ."frmjcse_article`(`id`)
        ON DELETE CASCADE
)
CHARSET=utf8 AUTO_INCREMENT=1";

OW::getDbo()->query($sql);

$sql = "CREATE TABLE `" . OW_DB_PREFIX . "frmjcse_citation_format` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `format` TEXT,
    PRIMARY KEY (`id`)
)
CHARSET=utf8 AUTO_INCREMENT=1";

OW::getDbo()->query($sql);
