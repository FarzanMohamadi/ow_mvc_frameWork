<?php
try {
    OW::getAuthorization()->deleteAction('video', 'delete_comment_by_content_owner');
}
catch ( Exception $e ) { }

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__).DS.'langs.zip', 'video');