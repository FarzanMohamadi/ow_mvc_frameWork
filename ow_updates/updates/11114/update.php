<?php
$query = 'TRUNCATE TABLE  `'.OW_DB_PREFIX.'base_user_reset_password`';
Updater::getDbo()->query($query);