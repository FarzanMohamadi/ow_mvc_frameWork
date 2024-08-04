<?php
$config = OW::getConfig();
if ($config->configExists('frmgroupsplus', 'show_otp_form')) {
    $config->deleteConfig('frmgroupsplus', 'show_otp_form');
}