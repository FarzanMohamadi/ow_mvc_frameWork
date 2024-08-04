<?php
OW::getConfig()->saveConfig('birthdays', 'users_birthday_event_ts', '0');
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'birthdays');