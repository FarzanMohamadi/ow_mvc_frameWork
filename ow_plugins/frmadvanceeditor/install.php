<?php
BOL_LanguageService::getInstance()->addPrefix('frmadvanceeditor','FRMAdvanceEditor');

$config = OW::getConfig();

if ( !$config->configExists('frmadvanceeditor', 'isCustomHtmlWidgetEditorAdvance') )
{
    $config->saveConfig('frmadvanceeditor', 'isCustomHtmlWidgetEditorAdvance', false);
}
