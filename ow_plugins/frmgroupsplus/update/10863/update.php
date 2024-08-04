<?php
try {
    $authorization = OW::getAuthorization();
    $groupName = 'frmgroupsplus';
    $authorization->addAction($groupName, 'add-forced-groups');
}catch (Exception $e){}
