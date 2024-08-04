<?php
/**
 * FRM Rules
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmrules
 * @since 1.0
 */

OW::getPluginManager()->addPluginSettingsRouteName('frmrules', 'frmrules.admin');

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'frmrules.index', 'frmrules', 'bottom_menu_item', OW_Navigation::VISIBLE_FOR_ALL);

// Mobile activation
OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmrules.index', 'frmrules', 'rules_mobile', OW_Navigation::VISIBLE_FOR_ALL);

// register sitemap entities
BOL_SeoService::getInstance()->addSitemapEntity('frmrules', 'frmrules_sitemap', 'frmrules', array(
    'rule_list'
));