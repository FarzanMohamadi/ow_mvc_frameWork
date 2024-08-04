<?php
try {
    $languageService = Updater::getLanguageService();

    $languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmvideoplus', 'download_file', 'دریافت ویدیو');
    $languageService->addOrUpdateValueByLanguageTag('en', 'frmvideoplus', 'download_file', 'download video');
}catch(Exception $e){

}