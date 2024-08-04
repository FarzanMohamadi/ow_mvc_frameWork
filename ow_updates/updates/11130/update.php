<?php
try {
    $query = "ALTER TABLE ".BOL_SitemapDao::getInstance()->getTableName()." AUTO_INCREMENT=1;";
    Updater::getDbo()->query($query);
}catch(Exception $e ){}

try {
    $query = "TRUNCATE TABLE ".BOL_SitemapDao::getInstance()->getTableName().";";
    Updater::getDbo()->query($query);
}catch(Exception $e ){}

try {
    $query = "ALTER TABLE ".BOL_SitemapDao::getInstance()->getTableName()." DROP INDEX `url`;";
    Updater::getDbo()->query($query);
}catch(Exception $e ){}

try {
    $query = "ALTER TABLE ".BOL_SitemapDao::getInstance()->getTableName()." MODIFY COLUMN url VARCHAR(512);";
    Updater::getDbo()->query($query);
}catch(Exception $e ){}