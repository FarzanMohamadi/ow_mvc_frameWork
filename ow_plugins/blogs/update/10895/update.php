<?php
$authorization = OW::getAuthorization();
$groupName = 'blogs';
$authorization->addAction($groupName, 'publish_notification');
