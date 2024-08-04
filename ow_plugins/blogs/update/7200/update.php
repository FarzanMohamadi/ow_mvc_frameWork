<?php
$authorization = OW::getAuthorization();
$groupName = 'blogs';
$authorization->deleteAction($groupName, 'delete_comment_by_content_owner');