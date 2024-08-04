<?php
$path = OW::getPluginManager()->getPlugin('frmjcse')->getRootDir() . 'langs.zip';
Updater::getLanguageService()->importPrefixFromZip($path, 'frmjcse');

try {
    $authorization = OW::getAuthorization();
    $groupName = 'frmjcse';
    $authorization->addGroup($groupName);
    $authorization->addAction($groupName, 'edit');
}
catch (Exception $e){

}