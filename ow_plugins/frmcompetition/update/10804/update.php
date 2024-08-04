<?php
try {
    $authorization = OW::getAuthorization();
    $groupName = 'frmcompetition';
    $authorization->deleteAction($groupName, 'competition-add_competition');
    $authorization->deleteAction($groupName, 'competition-add_group_point');
    $authorization->deleteAction($groupName, 'competition-add_user_point');
}catch (Exception $e){}