<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 11/5/2017
 * Time: 11:26 AM
 */
$config = OW::getConfig();
if ( !$config->configExists('frmsecurityessentials', 'update_all_plugins_activated') )
{
    $config->addConfig('frmsecurityessentials', 'update_all_plugins_activated', true);
}

$languageService = Updater::getLanguageService();
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmsecurityessentials','allow_update_all_plugins', 'امکان به‌روزرسانی تمام افزونه‌ها به صورت یکجا');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'admin', 'plugin_manual_update_all_button_label', 'به‌روزرسانی تمام افزونه‌ها');
$languageService->addOrUpdateValueByLanguageTag('en', 'admin', 'plugin_manual_update_all_button_label', 'Update all plugins');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmsecurityessentials','allow_update_all_plugins', 'Update all plugins once');