<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */

class FRMTELEGRAM_BOL_TelegramEntry extends OW_Entity
{
    public
        $chatId,
        $entryId,
        $authorName,
        $entry,
        $timestamp,
        $isFile,
        $fileCaption,
        $isDeleted;
}