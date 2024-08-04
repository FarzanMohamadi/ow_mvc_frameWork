<?php
try {
    $languageService = Updater::getLanguageService();

    $languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmrss', 'view_link', 'مشاهده پیوند');
    $languageService->addOrUpdateValueByLanguageTag('en', 'frmrss', 'view_link', 'view link');
}catch(Exception $e){

}