<?php
/**
 * frmmobileaccount
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobileaccount
 * @since 1.0
 */

OW::getConfig()->saveConfig('frmmobileaccount', 'expired_cookie', '10');
OW::getConfig()->saveConfig('frmmobileaccount', 'login_type_version', '1');
OW::getConfig()->saveConfig('frmmobileaccount', 'join_type_version', '1');
OW::getConfig()->saveConfig('frmmobileaccount', 'mandatory_email', false);
OW::getConfig()->saveConfig('frmmobileaccount', 'username_prefix', 'shub_user_');
OW::getConfig()->saveConfig('frmmobileaccount', 'email_postfix', '@shub.frmcenter.ir');
