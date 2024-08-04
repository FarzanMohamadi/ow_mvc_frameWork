<?php
/**
 * Issa Annamoradnejad
 */
$authorization = OW::getAuthorization();
$groupName = 'forum';
$authorization->addAction($groupName, 'add_comment');