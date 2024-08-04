<?php

try
{
    $authorization = OW::getAuthorization();
    $groupName = 'broadcast';
    $authorization->addGroup($groupName);
    $authorization->addAction($groupName, 'send-message-from-outside');
}
catch ( LogicException $e ) {}