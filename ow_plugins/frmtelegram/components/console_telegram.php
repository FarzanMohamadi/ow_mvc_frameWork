<?php
/**
 * frmtelegram
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */
class FRMTELEGRAM_CMP_ConsoleTelegram extends BASE_CMP_ConsoleDropdownList
{
    public function __construct()
    {
        parent::__construct( OW::getLanguage()->text('frmtelegram', 'telegram_title'), 'telegram' );
        $plugin = OW::getPluginManager()->getPlugin('frmtelegram');
        $this->setTemplate($plugin->getCmpViewDir() . 'console_html_template.html');
        if (OW::getConfig()->getValue('frmtelegram', 'icon_type') == 2) {
            $this->assign('telegramURL', "https://telegram.me/" . OW::getConfig()->getValue('frmtelegram', 'link'));
        }
        else if (OW::getConfig()->getValue('frmtelegram', 'icon_type') == 3) {
            $this->assign('telegramURL', OW::getRouter()->urlForRoute('frmtelegram.messages'));
        }
    }

    protected function initJs()
    {
        parent::initJs();
        return $this->consoleItem->getUniqId();
    }

}