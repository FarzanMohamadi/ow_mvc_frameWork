<?php

try
{
    $authorization = OW::getAuthorization();
    $groupName = 'broadcast';
    $authorization->deleteAction($groupName, 'send-message-from-outside');
    $authorization->deleteGroup($groupName);
}
catch ( LogicException $e ) {}