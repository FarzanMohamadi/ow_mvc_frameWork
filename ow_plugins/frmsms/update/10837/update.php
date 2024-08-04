<?php
$config = OW::getConfig();
if (!$config->configExists('frmsms', 'remove_text_link')) {
    $config->saveConfig('frmsms', 'remove_text_link', 0);
}
