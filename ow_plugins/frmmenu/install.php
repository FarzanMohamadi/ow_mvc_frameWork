<?php
/**
 * FRM Menu
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmenu
 * @since 1.0
 */

if (!OW::getConfig()->configExists('frmmenu', 'replaceMenu')){
    OW::getConfig()->saveConfig('frmmenu', 'replaceMenu', false);
}
