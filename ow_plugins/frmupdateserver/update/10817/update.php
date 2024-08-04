<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmupdateserver', 'download_last_build_version_label', 'نسخه {$value}');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmupdateserver', 'download_last_build_version_label', 'Version {$value}');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmupdateserver', 'download_last_core_version_title', 'بارگیری آخرین نسخه');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmupdateserver', 'download_last_core_version_title', 'Download the latest version');

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmupdateserver', 'download_last_core_update_version_title', 'بارگیری نسخه به‌روزرسانی به آخرین نسخه');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmupdateserver', 'download_last_core_update_version_title', 'Download the updating files for the last version');