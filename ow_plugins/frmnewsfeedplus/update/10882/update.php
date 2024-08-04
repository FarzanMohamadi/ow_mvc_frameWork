<?php
/**
 * Created by PhpStorm.
 * User: Farzan Mohammadi
 * Date: 5/22/2019
 * Time: 12:03 PM
 */

try {
    OW::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frmnewsfeedplus_thumbnail` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `attachmentId` int(11) NOT NULL,
    `name` VARCHAR(200) NOT NULL,
    `userId` int(11) NOT NULL,
    `creationTime` int(11),
    PRIMARY KEY (`id`)
    )DEFAULT CHARSET=utf8');
} catch (Exception $e) {}

$thumbnailUrl = OW::getPluginManager()->getPlugin('frmnewsfeedplus')->getUserFilesDir();
if (OW::getStorage()->isDir($thumbnailUrl)) {
    $formerThumbnailUrls = OW::getStorage()->getFileNameList($thumbnailUrl);
    $formerThumbnailNames = str_replace($thumbnailUrl, "", $formerThumbnailUrls);
    $formerThumbnailIds = str_replace(".png", "", $formerThumbnailNames);

    foreach ($formerThumbnailIds as $key => $thumbnail) {
        FRMNEWSFEEDPLUS_BOL_ThumbnailDao::getInstance()->addThumbnail($thumbnail, UTIL_String::getRandomString(10) . '.png', -1);
    }
}
