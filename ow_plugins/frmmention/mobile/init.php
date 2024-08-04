<?php
/**
 * frmmention
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmention
 * @since 1.0
 */
FRMMENTION_MCLASS_EventHandler::getInstance()->genericInit();

OW::getRouter()->addRoute(new OW_Route('frmmention.load_usernames', 'frmmention/usernames', "FRMMENTION_CTRL_Load", 'loadUsernames'));
OW::getRouter()->addRoute(new OW_Route('frmmention.load_usernames_filled', 'frmmention/usernames/:username', "FRMMENTION_CTRL_Load", 'loadUsernames'));
