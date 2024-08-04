<?php

$sql = "ALTER TABLE `".OW_DB_PREFIX."newsfeed_action_set` 
   ADD KEY `userId` (`userId`)";

Updater::getDbo()->query($sql);