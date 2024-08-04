<?php

OW::getDbo()->query("
DELETE FROM `" . FRMSecurityProvider::$prefixBackuplabel . OW_DB_PREFIX . "frmsms_token`;");

OW::getDbo()->query("ALTER TABLE `" . FRMSecurityProvider::$prefixBackuplabel .  OW_DB_PREFIX ."frmsms_token` MODIFY `token` varchar(64);");
