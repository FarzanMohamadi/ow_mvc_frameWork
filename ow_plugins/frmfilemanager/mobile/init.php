<?php
/**
 * frmfilemanager
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmfilemanager
 * @since 1.0
 */

OW::getRouter()->addRoute(new OW_Route('frmfilemanager.saveToProfile', 'frmfilemanager/saveToProfile', "FRMFILEMANAGER_CTRL_Backend", 'saveToProfile'));

FRMFILEMANAGER_MCLASS_EventHandler::getInstance()->init();