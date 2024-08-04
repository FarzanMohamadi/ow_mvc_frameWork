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
    $languageService->addOrUpdateValue($langFaId, 'frmsecurityessentials', 'auth_action_label_view_users_list', 'مشاهده فهرست اعضا');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmsecurityessentials', 'auth_action_label_view_users_list', 'View users list');
}

