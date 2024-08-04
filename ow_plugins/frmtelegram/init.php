<?php
/**
 * frmtelegram
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */
OW::getRouter()->addRoute(new OW_Route('frmtelegram.admin', 'frmtelegram/admin', "FRMTELEGRAM_CTRL_Admin", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.admin.deactivate-item', 'frmtelegram/admin/deactivate-item/:id', 'FRMTELEGRAM_CTRL_Admin', 'deactivateItem'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.admin.activate-item', 'frmtelegram/admin/activate-item/:id', 'FRMTELEGRAM_CTRL_Admin', 'activateItem'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.admin.ajax-save-order', 'frmtelegram/admin/ajax-save-order', 'FRMTELEGRAM_CTRL_Admin', 'ajaxSaveOrder'));


OW::getRouter()->addRoute(new OW_Route('frmtelegram.messages', 'telegram', "FRMTELEGRAM_CTRL_Feed", 'index'));//, array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'group'))));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.messages.list', 'telegram/list/:list', "FRMTELEGRAM_CTRL_Feed", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.item.delete', 'telegram/delete/:id', "FRMTELEGRAM_CTRL_Feed", 'deleteItem'));


OW::getRouter()->addRoute(new OW_Route('frmtelegram.load.more.empty', 'telegram/load_more/:chatId', "FRMTELEGRAM_CTRL_Feed", 'loadMore'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.load.older.empty', 'telegram/load_older/:chatId', "FRMTELEGRAM_CTRL_Feed", 'loadOlder'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.load.more', 'telegram/load_more/:chatId/:id', "FRMTELEGRAM_CTRL_Feed", 'loadMore'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.load.older', 'telegram/load_older/:chatId/:id', "FRMTELEGRAM_CTRL_Feed", 'loadOlder'));


OW::getRouter()->addRoute(new OW_Route('frmtelegram.widget.load.more.empty', 'telegram/widget/load_more/:chatId', "FRMTELEGRAM_CTRL_Feed", 'widgetLoadMore'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.widget.load.older.empty', 'telegram/widget/load_older/:chatId', "FRMTELEGRAM_CTRL_Feed", 'widgetLoadOlder'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.widget.load.more', 'telegram/widget/load_more/:chatId/:id', "FRMTELEGRAM_CTRL_Feed", 'widgetLoadMore'));
OW::getRouter()->addRoute(new OW_Route('frmtelegram.widget.load.older', 'telegram/widget/load_older/:chatId/:id', "FRMTELEGRAM_CTRL_Feed", 'widgetLoadOlder'));


FRMTELEGRAM_CLASS_EventHandler::getInstance()->init();
