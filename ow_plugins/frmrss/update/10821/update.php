<?php
try {
    $languageService = Updater::getLanguageService();

    $languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmrss', 'rss_generate_disc', 'برای ایجاد خبرخوان، پس از انتخاب برچسب مورد نظر، دکمه مشاهده پیوند را انتخاب کنید تا خبرخوان موردنظر تولید شود.');
    $languageService->addOrUpdateValueByLanguageTag('en', 'frmrss', 'rss_generate_disc', 'To generate desired RSS link, enter specific label and then press view link button.');
}catch(Exception $e){

}