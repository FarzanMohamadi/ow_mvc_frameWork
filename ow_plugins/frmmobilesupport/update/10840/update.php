<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

if (!OW::getConfig()->configExists('frmmobilesupport', 'last_firebase_send_notifications_time')){
    OW::getConfig()->saveConfig('frmmobilesupport', 'last_firebase_send_notifications_time', '0');
}