<?php

OW::getDbo()->query("
DELETE FROM `" . OW_DB_PREFIX . "frmsms_token`;");

OW::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "frmsms_token` MODIFY `token` varchar(64);");
