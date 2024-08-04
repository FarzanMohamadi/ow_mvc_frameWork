<?php
/**
 * frmtelegram
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */
OW::getRouter()->addRoute(new OW_Route('frmtelegram.messages', 'telegram', "FRMTELEGRAM_MCTRL_Feed", 'index'));//, array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'group'))));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.messages.list', 'telegram/list/:list', "FRMTELEGRAM_MCTRL_Feed", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.item.delete', 'telegram/delete/:id', "FRMTELEGRAM_MCTRL_Feed", 'deleteItem'));

OW::getRouter()->addRoute(new OW_Route('frmtelegram.load.more.empty', 'telegram/load_more/:chatId', "FRMTELEGRAM_MCTRL_Feed", 'loadMore'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.load.older.empty', 'telegram/load_older/:chatId', "FRMTELEGRAM_MCTRL_Feed", 'loadOlder'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.load.more', 'telegram/load_more/:chatId/:id', "FRMTELEGRAM_MCTRL_Feed", 'loadMore'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.load.older', 'telegram/load_older/:chatId/:id', "FRMTELEGRAM_MCTRL_Feed", 'loadOlder'));