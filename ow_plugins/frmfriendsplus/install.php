<?php
$config = OW::getConfig();
if (!$config->configExists('frmfriendsplus', 'selected_roles')) {
    $config->addConfig('frmfriendsplus', 'selected_roles', null);
}
