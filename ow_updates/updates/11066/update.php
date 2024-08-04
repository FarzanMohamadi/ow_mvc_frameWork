<?php
$files = [
    OW_DIR_ROOT . 'install_info.txt',
    OW_DIR_ROOT . 'UPDATE.txt',
    OW_DIR_ROOT . 'oxwall1.8-db-backup.sql'];
foreach($files as $file){
    OW::getStorage()->removeFile($file);
}

