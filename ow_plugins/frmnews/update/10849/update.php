<?php
OW::getDbo()->query("ALTER TABLE `" . OW_DB_PREFIX . "frmnews_entry` MODIFY entry mediumtext Not Null");