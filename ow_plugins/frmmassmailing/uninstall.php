<?php
$config = OW::getConfig();
if($config->configExists('frmmassmailing', 'mail_view_count'))
{
    $config->deleteConfig('frmmassmailing', 'mail_view_count');
}