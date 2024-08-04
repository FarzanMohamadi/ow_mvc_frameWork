<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

$sql = "UPDATE `".OW_DB_PREFIX."notifications_notification` 
   SET `viewed`=1
   WHERE `entityType`='user-edit-approve';";

Updater::getDbo()->query($sql);