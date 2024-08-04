<?php
try {
    $authorization = OW::getAuthorization();
    $groupName = 'frmgroupsplus';
    $authorization->addGroup($groupName);

    $authorization->addAction($groupName, 'all-search');
    $authorization->addAction($groupName, 'direct-add');
}catch (Exception $e){}

$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmgroupsplus', 'group_managers', 'مدیران گروه');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmgroupsplus', 'group_managers', 'Group managers');

$languages = $languageService->getLanguages();
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
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'auth_action_label_direct_add', 'افزودن کاربران به گروه بدون نیاز به تایید');
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'auth_frminvite_label', 'گروه پلاس');
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'auth_action_label_all_search', 'مشاهده کلیه کاربران سامانه به هنگام دعوت به گروه');
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'add_to_group_title', 'افزودن به گروه');
}

if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmgroupsplus', 'auth_action_label_direct_add', 'Add members to a group without needing of memebrs acceptance');
    $languageService->addOrUpdateValue($langEnId, 'frmgroupsplus', 'auth_frminvite_label', 'frmgroupsplus');
    $languageService->addOrUpdateValue($langEnId, 'frmgroupsplus', 'auth_action_label_all_search', 'View all members to invite to a group');
    $languageService->addOrUpdateValue($langEnId, 'frmgroupsplus', 'add_to_group_title', 'Add to group');
}