<?php
$query = "UPDATE " . BOL_CommentEntityDao::getInstance()->getTableName() . " SET `entityType`='forum-post' WHERE `entityType`='topic-posts' AND `pluginKey`='forum' ";
Updater::getDbo()->query($query);