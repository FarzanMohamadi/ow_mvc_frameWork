<?php
$query = " DELETE FROM " . OW_DB_PREFIX . "base_authorization_action WHERE `name`='send_message' ";
Updater::getDbo()->query($query);

$query = " DELETE FROM " . OW_DB_PREFIX . "base_authorization_action WHERE `name`='read_message' ";
Updater::getDbo()->query($query);

$query = " DELETE FROM " . OW_DB_PREFIX . "base_authorization_action WHERE `name`='reply_to_message' ";
Updater::getDbo()->query($query);