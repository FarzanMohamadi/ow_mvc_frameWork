<?php
$query = " DELETE FROM " . OW_DB_PREFIX . "base_authorization_action WHERE `name`='send_chat_message' ";
Updater::getDbo()->query($query);

$query = " DELETE FROM " . OW_DB_PREFIX . "base_authorization_action WHERE `name`='read_chat_message' ";
Updater::getDbo()->query($query);

$query = " DELETE FROM " . OW_DB_PREFIX . "base_authorization_action WHERE `name`='reply_to_chat_message' ";
Updater::getDbo()->query($query);