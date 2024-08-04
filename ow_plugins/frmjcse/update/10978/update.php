<?php
try{
    $sql = "CREATE TABLE `" . OW_DB_PREFIX . "frmjcse_citation_format` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `title` VARCHAR(200) NOT NULL,
        `format` TEXT,
        PRIMARY KEY (`id`)
    )
    CHARSET=utf8 AUTO_INCREMENT=1";

    OW::getDbo()->query($sql);
}
catch (Exception $e){

}