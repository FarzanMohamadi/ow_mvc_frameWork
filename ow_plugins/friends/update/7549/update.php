<?php
$dbPrefix = OW_DB_PREFIX;
$sql = "UPDATE `{$dbPrefix}base_authorization_group` SET `moderated` = '0' WHERE `name` = 'friends'";
Updater::getDbo()->query($sql);
