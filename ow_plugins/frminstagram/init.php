<?php
/**
 * frminstagram
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frminstagram
 * @since 1.0
 */
OW::getRouter()->addRoute(new OW_Route('frminstagram.admin', 'frminstagram/admin', "FRMINSTAGRAM_CTRL_Admin", 'index'));
OW::getRouter()->addRoute(new OW_Route('frminstagram.widget_load', 'frminstagram/widget/load-data/:username', "FRMINSTAGRAM_CTRL_Instagram", 'widgetLoadJson'));
OW::getRouter()->addRoute(new OW_Route('frminstagram.widget_load_more', 'frminstagram/widget/load-more/:username', "FRMINSTAGRAM_CTRL_Instagram", 'loadMore'));

FRMINSTAGRAM_CLASS_EventHandler::getInstance()->init();
