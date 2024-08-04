<?php
$config = OW::getConfig();
if (!$config->configExists('frmgroupsplus', 'show_otp_form')) {
    $config->addConfig('frmgroupsplus', 'show_otp_form', 0);
}