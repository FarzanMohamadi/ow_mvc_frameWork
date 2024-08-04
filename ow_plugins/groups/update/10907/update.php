<?php
$sql = "UPDATE `".OW_DB_PREFIX."groups_group_user` SET `last_seen_action`='".time()."'";
Updater::getDbo()->query($sql);