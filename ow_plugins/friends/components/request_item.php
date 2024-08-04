<?php
 /**
 * @package ow_plugins.friends.components
 * @since 1.0
 */
class FRIENDS_CMP_RequestItem extends BASE_CMP_ConsoleListIpcItem
{
    public function __construct()
    {
        parent::__construct();

        $plugin = OW::getPluginManager()->getPlugin('BASE');
        $this->setTemplate($plugin->getCmpViewDir() . 'console_list_ipc_item.html');

        $this->addClass('ow_friend_request_item ow_cursor_default');
    }
}