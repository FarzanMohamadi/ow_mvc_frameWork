<?php
/**
 * User: Issa Annamoradnejad
 * Date: 8/19/2017
 */

$languageService = Updater::getLanguageService();
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmsecurityessentials', 'auth_action_label_privacy_alert', '            اعلان حریم خصوصی');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmsecurityessentials', 'auth_action_label_privacy_alert', 'Privacy Alert');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmsecurityessentials', 'auth_group_label', 'موارد امنیتی ضروری');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmsecurityessentials', 'auth_group_label', 'Security Essentials');
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmsecurityessentials', 'remember_me_default_value', 'فعال بودن مرا بخاطر داشته باش در صفحه ورود');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmsecurityessentials', 'remember_me_default_value', 'Activate remember me in sign-in page');

if ( !OW::getConfig()->configExists('frmsecurityessentials', 'remember_me_default_value') )
{
    OW::getConfig()->saveConfig('frmsecurityessentials', 'remember_me_default_value', false);
}