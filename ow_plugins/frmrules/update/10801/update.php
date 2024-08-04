<?php
if(BOL_NavigationService::getInstance()->findMenuItem('frmrules', 'rules_mobile')==null) {
    Updater::getNavigationService()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmrules.index', 'frmrules', 'rules_mobile', OW_Navigation::VISIBLE_FOR_ALL);

}
Updater::getSeoService()->addSitemapEntity('frmrules', 'frmrules_sitemap', 'frmrules', array(
    'rule_list'
));
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'frmrules');