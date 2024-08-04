<?php
Updater::getWidgetService()->deleteWidget('EVENT_CMP_MyEvents');

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'event');
