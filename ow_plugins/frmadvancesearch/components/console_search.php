<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancesearch.classes
 * @since 1.0
 */
class FRMADVANCESEARCH_CMP_ConsoleSearch extends BASE_CMP_ConsoleDropdownList
{
    public function __construct()
    {
        parent::__construct( OW::getLanguage()->text('frmadvancesearch', 'search_title'), 'search' );
        $plugin = OW::getPluginManager()->getPlugin('frmadvancesearch');
        $this->setTemplate($plugin->getCmpViewDir() . 'console_dropdown_list.html');
        $this->assign('searchUrl', 'frmsearch_createSearchElements();');

    }

    protected function initJs()
    {
//        parent::initJs(); do not use parent initJs to deactivate the default menuItem js
        return $this->consoleItem->getUniqId();
    }

}