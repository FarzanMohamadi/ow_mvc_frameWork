<?php
$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langEnId = null;
$langFaId = null;
foreach ($languages as $lang) {
    if ($lang->tag == 'fa-IR') {
        $langFaId = $lang->id;
    }
    if ($lang->tag == 'en') {
        $langEnId = $lang->id;
    }
}

if ($langFaId != null) {
    $languageService->addOrUpdateValue($langFaId, 'frminvite', 'createInvitationLink', 'ایجاد لینک دعوت');
    $languageService->addOrUpdateValue($langFaId, 'frminvite', 'create_invite_link_description', 'برای ایجاد لینک دعوت به سامانه گزینه ایجاد لینک را انتخاب کنید');
    $languageService->addOrUpdateValue($langFaId, 'frminvite', 'create_invite_link', 'ایجاد لینک دعوت');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frminvite', 'createInvitationLink', 'create invitation link');
    $languageService->addOrUpdateValue($langEnId, 'frminvite', 'create_invite_link_description', 'select create link button for invitation link ');
    $languageService->addOrUpdateValue($langEnId, 'frminvite', 'create_invite_link', 'create invitation link');
}
