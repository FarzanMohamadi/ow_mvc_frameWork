<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */


$updateDir = dirname(__FILE__) . DS;
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'frmcommentplus');
