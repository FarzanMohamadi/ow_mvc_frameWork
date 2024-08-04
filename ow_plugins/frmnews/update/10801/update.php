<?php
if(BOL_NavigationService::getInstance()->findMenuItem('frmnews', 'frmnews_mobile')==null) {
    Updater::getNavigationService()->addMenuItem(OW_Navigation::MOBILE_TOP, 'frmnews-default', 'frmnews', 'frmnews_mobile', OW_Navigation::VISIBLE_FOR_ALL);
}
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'frmnews');

