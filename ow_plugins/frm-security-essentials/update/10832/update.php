<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

try {

    Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'frmsecurityessentials');
    $authorization = OW::getAuthorization();
    $groupName = 'frmsecurityessentials';
    $authorization->addGroup($groupName);
    $authorization->addAction($groupName, 'security-privacy_alert');

}catch(Exception $e){

}