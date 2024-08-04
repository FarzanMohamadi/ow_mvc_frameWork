<?php
try {
    $authorization = OW::getAuthorization();
    $groupName = 'frmgroupsplus';
    $authorization->addAction($groupName, 'group_approve',false,false);
}catch (Exception $e){}
