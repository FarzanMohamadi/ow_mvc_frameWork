<?php
try{
    $authorization = OW::getAuthorization();
    $groupName = 'frmsecurityessentials';
    if(BOL_AuthorizationService::getInstance()->findAction($groupName, 'customize_user_profile') === null){
        $authorization->addAction($groupName, 'customize_user_profile');
    }
}catch (Exception $e){

}
