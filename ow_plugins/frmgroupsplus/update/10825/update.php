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
    $languageService->addOrUpdateValue($langFaId,'frmgroupsplus','group_user_invitation_notification','<a href="{$userUrl}">{$userName}</a> شما را به گروه <a href="{$groupUrl}">{$groupTitle}</a> دعوت کرده است');
    $languageService->addOrUpdateValue($langFaId,'frmgroupsplus','accept_group_invitation_notification','شما دعوتنامه عضویت در گروه <a href="{$groupUrl}">{$groupTitle}</a> را تاپید کردید');
    $languageService->addOrUpdateValue($langFaId,'frmgroupsplus','ignore_group_invitation_notification',' شما از پذیرش دعوتنامه عضویت در گروه <a href="{$groupUrl}">{$groupTitle}</a>  چشم پوشی کردید');
}
if ($langEn != null) {
    $languageService->addOrUpdateValue($langEn,'frmgroupsplus','group_user_invitation_notification','<a href="{$userUrl}">{$userName}</a> invite you to <a href="{$groupUrl}">{$groupTitle}</a> group');
    $languageService->addOrUpdateValue($langEn,'frmgroupsplus','accept_group_invitation_notification','You accept to join <a href="{$groupUrl}">{$groupTitle}</a> group');
    $languageService->addOrUpdateValue($langEn,'frmgroupsplus','ignore_group_invitation_notification','You decline to join <a href="{$groupUrl}">{$groupTitle}</a> group');
}