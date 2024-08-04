<?php
$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langFaId = null;
$langEn = null;
foreach ($languages as $lang) {
    if ($lang->tag == 'fa-IR') {
        $langFaId = $lang->id;
    }
    if ($lang->tag == 'en') {
        $langEn = $lang->id;
    }
}

if ($langFaId != null) {
    $languageService->addOrUpdateValue($langFaId,'frmphotoplus','email_notifications_setting_like','کسی تصویر ارسالی من را پسندید');
    $languageService->addOrUpdateValue($langFaId, 'frmphotoplus', 'multiple_photo_liked_notification', '<a href="{$userUrl}">{$user}</a>  تصاویر شما را پسندید');
    $languageService->addOrUpdateValue($langFaId, 'frmphotoplus', 'photo_liked_notification', '<a href="{$userUrl}">{$user}</a> تصویر شما را پسندید');
}
if ($langEn != null) {
    $languageService->addOrUpdateValue($langEn,'frmphotoplus','email_notifications_setting_like','Someone likes my photo');
    $languageService->addOrUpdateValue($langEn, 'frmphotoplus', 'multiple_photo_liked_notification', '<a href="{$userUrl}">{$user}</a> likes your photos');
    $languageService->addOrUpdateValue($langEn, 'frmphotoplus', 'photo_liked_notification', '<a href="{$userUrl}">{$user}</a> likes your photo');
}