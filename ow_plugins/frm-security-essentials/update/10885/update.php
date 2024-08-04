<?php
try{
    $authorization = OW::getAuthorization();
    $groupName = 'frmsecurityessentials';
    $authorization->addAction($groupName, 'user-can-view-comments',true);
}catch (Exception $e){

}
