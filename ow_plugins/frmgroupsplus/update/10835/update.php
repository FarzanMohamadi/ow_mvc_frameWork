<?php
try {

    OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmgroupsplus_channel` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `groupId` int(11) NOT NULL,
    PRIMARY KEY (`id`)
    ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

    } catch (Exception $e) {}

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
    $languageService->addOrUpdateValue($langFaId,'frmgroupsplus','who_can_create_content','کسانی که می‌توانند محتوا ایجاد کنند');
    $languageService->addOrUpdateValue($langFaId,'frmgroupsplus','form_who_can_create_content_creators','فقط مدیران گروه');
    $languageService->addOrUpdateValue($langFaId,'frmgroupsplus','form_who_can_create_content_participants','شرکت کنندگان');
}
if ($langEn != null) {
    $languageService->addOrUpdateValue($langEn,'frmgroupsplus','who_can_create_content','Who can create content');
    $languageService->addOrUpdateValue($langEn,'frmgroupsplus','form_who_can_create_content_creators','Group administrators only');
    $languageService->addOrUpdateValue($langEn,'frmgroupsplus','form_who_can_create_content_participants','Participants');
}