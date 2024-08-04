<?php
try
{
    $sql = "REPLACE INTO `" . OW_DB_PREFIX . "base_preference` (`key`, `defaultValue`, `sectionName`, `sortOrder`) VALUES
                    ('mailbox_create_conversation_stamp', '0', 'general', 1),
                    ('mailbox_create_conversation_display_capcha', '0', 'general', 1)";

    Updater::getDbo()->query($sql);
}
catch ( Exception $ex )
{
    $errors[] = $ex;
}

OW::getPluginManager()->addPluginSettingsRouteName('mailbox', 'mailbox_admin_config');

if ( !OW::getConfig()->configExists('mailbox', 'enable_attachments') )
{
    OW::getConfig()->saveConfig('mailbox', 'enable_attachments', true, 'Enable file attachments');
}

if ( !OW::getConfig()->configExists('mailbox', 'upload_max_file_size') )
{
    OW::getConfig()->saveConfig('mailbox', 'upload_max_file_size', 2, 'Max upload file size(Mb)');
}

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'mailbox');

if ( !OW::getConfig()->configExists('mailbox', 'update_to_revision_3081') )
{
    OW::getConfig()->saveConfig('mailbox', 'update_to_revision_3081', 1, '');
}

if ( !OW::getConfig()->configExists('mailbox', 'last_updated_id') )
{
    OW::getConfig()->saveConfig('mailbox', 'last_updated_id', 0, '');
}

if ( !empty($errors) )
{
    printVar($errors);
}
