<?php
$sql = "CREATE TABLE `" . OW_DB_PREFIX . "mailbox_user_last_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `data` longtext,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8";

Updater::getDbo()->query($sql);