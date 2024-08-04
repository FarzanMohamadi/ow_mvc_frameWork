<?php
$config = OW::getConfig();

if ( !$config->configExists('frmadvanceeditor', 'isCustomHtmlWidgetEditorAdvance') )
{
    $config->saveConfig('frmadvanceeditor', 'isCustomHtmlWidgetEditorAdvance', false);
}
