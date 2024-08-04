<?php
$config = OW::getConfig();
if ( !$config->configExists('frmjalali', 'dateLocale') )
{
    $config->addConfig('frmjalali', 'dateLocale',1);
}
