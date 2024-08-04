<?php
$languageService = Updater::getLanguageService();

$languageService->deleteLangKey('photo', 'album_desc');
$languageService->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'photo');
