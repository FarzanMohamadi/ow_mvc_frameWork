<?php
$updateDir = dirname(__FILE__) . DS;
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'frmgroupsplus');
try
{
    $authorization = OW::getAuthorization();
    $groupName = 'groups';
    $authorization->addAction($groupName, 'groups-add-file');
}
catch ( LogicException $e ) {}