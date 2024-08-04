<?php
$path = OW::getPluginManager()->getPlugin('frmjcse')->getRootDir() . 'langs.zip';
Updater::getLanguageService()->importPrefixFromZip($path, 'frmjcse');
