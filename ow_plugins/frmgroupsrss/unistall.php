<?php

$config = OW::getConfig();

if($config->configExists('frmgroupsrss', 'update_interval'))
    $config->deleteConfig('frmgroupsrss', 'update_interval');

if($config->configExists('frmgroupsrss', 'feeds_count'))
    $config->deleteConfig('frmgroupsrss', 'feeds_count');
