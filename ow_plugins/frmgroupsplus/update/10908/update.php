
<?php
$config = OW::getConfig();

if ($config->configExists('frmgroupsplus', 'unapprovedGroupsList')) {
    $config->deleteConfig('frmgroupsplus', 'unapprovedGroupsList');
}
