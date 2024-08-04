<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 6/26/2017
 */

if (!OW::getConfig()->configExists('frmmobilesupport', 'custom_download_link_code')){
    OW::getConfig()->saveConfig('frmmobilesupport', 'custom_download_link_code', '<a class="app_download_link android" href="" target="_blank"></a>');
}

if (!OW::getConfig()->configExists('frmmobilesupport', 'custom_download_link_activation')){
    OW::getConfig()->saveConfig('frmmobilesupport', 'custom_download_link_activation', false);
}

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
    $languageService->addOrUpdateValue($langFaId, 'frmmobilesupport', 'download_show', ' نمایش دریافت');
    $languageService->addOrUpdateValue($langFaId, 'frmmobilesupport', 'custom_download_link_label', ' کد HTML لینک‌های مرتبط');
    $languageService->addOrUpdateValue($langFaId, 'frmmobilesupport', 'custom_download_link_desc', '    سایر لینک‌های دریافت نرم‌افزار از طریق لینک‌های خارجی مانند بازار را به صورت کد HTML در این قسمت وارد کنید.');
    $languageService->addOrUpdateValue($langFaId, 'frmmobilesupport', 'download_activation', 'فعال‌سازی دکمه‌های دریافت در بخش فوتر');
}
if ($langEnId != null) {
    $languageService->addOrUpdateValue($langEnId, 'frmmobilesupport', 'download_show', 'Download show');
    $languageService->addOrUpdateValue($langEnId, 'frmmobilesupport', 'custom_download_link_label', 'custom Links\' HTML');
    $languageService->addOrUpdateValue($langEnId, 'frmmobilesupport', 'custom_download_link_desc', 'Enter other related download links such as app store as HTML code');
    $languageService->addOrUpdateValue($langEnId, 'frmmobilesupport', 'download_activation', 'Activate download buttons in footer');
}