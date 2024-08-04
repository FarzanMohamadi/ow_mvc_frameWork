<?php
Updater::getLanguageService()->updatePrefixForPlugin('frmsecurityessentials');
try{
    $authorization = OW::getAuthorization();
    $groupName = 'frmsecurityessentials';
    $authorization->addAction($groupName, 'customize_user_profile');
}catch (Exception $e){

}
