<?php
try {
    $authorization = OW::getAuthorization();
    $groupName = 'frmmobilesupport';
    $authorization->deleteAction($groupName, 'show-desktop-version');
}catch (Exception $e){}