<?php
try {
    $authorization = OW::getAuthorization();
    $groupName = 'frmgroupsplus';
    $authorization->addAction($groupName, 'create_group_without_approval_need',false,false);
}catch (Exception $e){}
