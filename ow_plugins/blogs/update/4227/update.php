<?php
$exArr = array();

$start = 0;
$count = 100;

while ( true )
{
    $postList = array();
    
    $sql = "SELECT * FROM `".OW_DB_PREFIX."blogs_post` 
        ORDER BY `timestamp` ASC LIMIT :start, :count";

    try
    {
        $postList = Updater::getDbo()->queryForList($sql, array('start' => $start, 'count' => $count));
    }
    catch ( Exception $e ){ $exArr[] = $e; }

    if ( empty($postList) )
    {
        break;
    }

    foreach ( $postList as $post )
    {
        $sql = "UPDATE `".OW_DB_PREFIX."blogs_post` 
            SET `post` = :post 
            WHERE `id` = :id";
        
        try
        {
            Updater::getDbo()->query($sql, array('post' => nl2br($post['post']), 'id' => $post['id']));
        }
        catch ( Exception $e ){ $exArr[] = $e; }
    }
    
    $start += $count;
}

