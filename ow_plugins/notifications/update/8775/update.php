<?php
for ($i = 20; $i >= 0; $i--) {
    NOTIFICATIONS_BOL_NotificationDao::getInstance()->deleteCorruptedNotificationData(0 + $i * 1000, 1000);
}
