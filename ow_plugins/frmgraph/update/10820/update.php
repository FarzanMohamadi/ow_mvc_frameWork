<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

try {
    $q = 'ALTER TABLE `' . OW_DB_PREFIX . 'frmgraph_node`
          ADD COLUMN `user_all_likes_count` int(11) null default null AFTER `news_count`,
          ADD COLUMN `user_all_comments_count` int(11) null default null AFTER `user_all_likes_count`,
          ADD COLUMN `all_done_likes_count` int(11) null default null AFTER `user_all_comments_count`,
          ADD COLUMN `all_done_comments_count` int(11) null default null AFTER `all_done_likes_count`
          ';
    Updater::getDbo()->query($q);
}catch (Exception $ex){}
