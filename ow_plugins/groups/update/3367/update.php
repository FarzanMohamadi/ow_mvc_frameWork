<?php
$updateDir = dirname(__FILE__) . DS;
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'groups');

OW::getAuthorization()->addAction('groups', 'view', true);