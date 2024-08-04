<?php
/**
 * User: Farzan Mohammadi
 * Date: 4/11/18
 * Time: 9:18 AM
 */

OW::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "frmsms_token` MODIFY userId int(11);");