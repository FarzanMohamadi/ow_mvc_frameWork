<?php
$config = OW::getConfig();
if($config->configExists('frmemailcontroller', 'valid_email_services'))
{
    $config->deleteConfig('frmemailcontroller', 'valid_email_services');
}
if($config->configExists('frmemailcontroller', 'disable_frmemailcontroller'))
{
    $config->deleteConfig('frmemailcontroller', 'disable_frmemailcontroller');
}