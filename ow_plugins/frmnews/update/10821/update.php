<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/25/2017
 * Time: 10:29 AM
 */

$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmnews','search_by_entry_placeholder', 'محتوای مورد نظر خود را وارد کنید');
$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmnews','search_by_tag_placeholder', 'برچسب مورد نظر خود را وارد کنید');

$languageService->addOrUpdateValueByLanguageTag('en', 'frmnews','search_by_entry_placeholder', 'Enter text to search');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmnews','search_by_tag_placeholder', 'Enter tag to search');
