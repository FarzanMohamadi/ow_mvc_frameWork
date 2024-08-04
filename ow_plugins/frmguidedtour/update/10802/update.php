<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/25/2017
 * Time: 10:29 AM
 */

$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('en', 'frmguidedtour','guide_title', '{$site_name} guided tour');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmguidedtour','button_activateGuideline', 'Guided tour');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmguidedtour','button_next', 'Next');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmguidedtour','button_prev', 'Previous');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmguidedtour','button_nextPage', 'Next page guide');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmguidedtour','button_previousPage', 'Previous page guide');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmguidedtour','button_skip', 'Ignore guide');
$languageService->addOrUpdateValueByLanguageTag('en', 'frmguidedtour','page_unavailable', 'No guided tour is available for this page.');