<?php
/**
 * FRM Terms
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmterms
 * @since 1.0
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmterms', 'frmterms.admin');

OW::getNavigation()->addMenuItem(OW_Navigation::BOTTOM, 'frmterms.index', 'frmterms', 'bottom_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_BOTTOM, 'frmterms.index', 'frmterms', 'mobile_bottom_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
// register sitemap entities
BOL_SeoService::getInstance()->addSitemapEntity('frmterms', 'frmterms_sitemap', 'frmterms', array(
    'section'
));