<?php
$dbo = Updater::getDbo();

$sql = "ALTER TABLE `".OW_DB_PREFIX."mailbox_message` MODIFY  `text` TEXT;";
$dbo->query($sql);
