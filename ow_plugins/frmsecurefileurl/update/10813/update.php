<?php
$dbo = Updater::getDbo();
$query = "ALTER TABLE `".OW_DB_PREFIX."frmsecurefileurl_urls` MODIFY `hash` LONGTEXT;";
$dbo->query($query);
