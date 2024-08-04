<?php
/**
 * Notification collector
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.admin.classes
 * @since 1.0
 */
class ADMIN_CLASS_NotificationCollector extends BASE_CLASS_EventCollector
{
    const NOTIFICATION_UPDATE = 'update';
    const NOTIFICATION_SETTINGS = 'settings';
    const NOTIFICATION_INFO = 'info';
    const NOTIFICATION_WARNING = 'warning';

    public function add( $item, $type = self::NOTIFICATION_INFO )
    {
        $this->data[] = array(
            'message' => $item,
            'type' => $type
        );
    }
}