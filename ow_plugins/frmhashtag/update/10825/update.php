<?php
/**
 * User: Ismail Mirvakili
 */

OW::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "frmhashtag_entity` ADD `context` VARCHAR(100) DEFAULT NULL");
