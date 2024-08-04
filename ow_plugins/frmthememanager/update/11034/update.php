<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmthememanager
 * @since 1.0
 */


try {
    FRMTHEMEMANAGER_BOL_Service::getInstance()->updateAllThemesList();
}catch (Exception $ex){}