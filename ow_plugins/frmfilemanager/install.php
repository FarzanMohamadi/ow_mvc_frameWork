<?php
OW::getDbo()->query("

DROP TABLE IF EXISTS `" . OW_DB_PREFIX . "frmfilemanager_file`;
CREATE TABLE IF NOT EXISTS `" . OW_DB_PREFIX . "frmfilemanager_file` (
  `id`        int(7) unsigned NOT NULL auto_increment,
  `parent_id` int(7) unsigned NOT NULL,
  `name`      varchar(255) NOT NULL,
  `content`   longblob NOT NULL,
  `size`      int(10) unsigned NOT NULL default '0',
  `mtime`     int(10) unsigned NOT NULL default '0',
  `mime`      varchar(256) NOT NULL default 'unknown',
  `read`      enum('1', '0') NOT NULL default '1',
  `write`     enum('1', '0') NOT NULL default '1',
  `locked`    enum('1', '0') NOT NULL default '0',
  `hidden`    enum('1', '0') NOT NULL default '0',
  `width`     int(5) NOT NULL default '0',
  `height`    int(5) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY         `parent_id`   (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
");

// reset_and_import function
OW::getDbo()->query("
    INSERT INTO `" . OW_DB_PREFIX . "frmfilemanager_file`
        (`id`, `parent_id`, `name`, `content`, `size`, `mtime`, `mime`,  `read`, `write`, `locked`, `hidden`, `width`, `height`) VALUES 
        ('1' ,         '0', 'root',        '',    '0',     '0','directory', '1',     '0',      '0',      '0',      '0',     '0')
    ;
");