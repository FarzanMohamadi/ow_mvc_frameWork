<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmclamav
 * @since 1.0
 */

OW::getConfig()->saveConfig('frmclamav', 'unknown_files_permission', false);
OW::getConfig()->saveConfig('frmclamav', 'socket_disable_decision', false);
OW::getConfig()->saveConfig('frmclamav', 'socket_host', '127.0.0.1');
OW::getConfig()->saveConfig('frmclamav', 'socket_port', 3310);

$authorization = OW::getAuthorization();
$groupName = 'frmclamav';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'check_file');