<?php

$dbo = Updater::getDbo();

$query = "select id, institution from `" . OW_DB_PREFIX . "frmjcse_article`; ";
$res = $dbo->queryForList($query);

foreach($res as $v){
    $paperId = $v['id'];
    $institutions = explode('#', $v['institution']);

    $query = "select id, name from `" . OW_DB_PREFIX . "frmjcse_author` where articleid={$paperId}; ";
    $authors = $dbo->queryForList($query);
    $i = 0;
    foreach ($authors as $author) {
        $authorId = $author['id'];
        $name = $author['name'];
        $affliation = $institutions[$i];
        // update new columns
        $query = "UPDATE `" . OW_DB_PREFIX . "frmjcse_author` 
            SET `affliation`='{$affliation}', `email`='' WHERE `id`={$authorId};";
        $dbo->query($query);

        $i = ($i + 1) % count($institutions);
    }
}
