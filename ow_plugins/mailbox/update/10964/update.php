<?php
$modes = array('chat');
$config = OW::getConfig();
$config->deleteConfig('mailbox', 'active_modes');
$config->addConfig('mailbox', 'active_modes', json_encode($modes));

BOL_PluginDao::getInstance()->deletePluginAdminSettingsRoute('mailbox');
