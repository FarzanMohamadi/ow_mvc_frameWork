<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 7/1/2017
 * Time: 4:17 PM
 */

$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langFaId = null;
foreach ($languages as $lang) {
    if ($lang->tag == 'fa-IR') {
        $langFaId = $lang->id;
    }
}

if ($langFaId != null) {
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'file_list_widget_empty', 'هیچ فایلی وجود ندارد.');
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'files_count', 'تعداد کل فایل‌ها');
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'feed_add_file_string', 'فایل <a href="{$fileUrl}">{$fileName}</a>  را در <a href="{$groupUrl}">{$groupTitle}</a> بارگذاری کرد');
    $languageService->addOrUpdateValue($langFaId, 'frmgroupsplus', 'notif_add_file_string', '<a href="{$userUrl}">{$userName}</a>  فایل <a href="{$groupUrl}">{$fileName}</a>  را در <a href="{$groupUrl}">{$groupTitle}</a> بارگذاری کرد');
}