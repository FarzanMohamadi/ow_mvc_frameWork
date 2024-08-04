<?php
 Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'friends');

Updater::getWidgetService()->deleteWidget('FRIENDS_CMP_Widget');