<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmguidedtour
 * @since 1.0
 */
class FRMGUIDEDTOUR_CMP_ConsoleGuidedtour extends BASE_CMP_ConsoleDropdownList
{
    public function __construct()
    {
        parent::__construct(OW::getLanguage()->text('frmguidedtour', 'guide_title'), 'guide');
        $plugin = OW::getPluginManager()->getPlugin('frmguidedtour');
        $this->setTemplate($plugin->getCmpViewDir() . 'console_html_template.html');
        $this->assign('guideUrl', "frmgt_setUnseenAndReload()");
    }

    protected function initJs()
    {
        //        parent::initJs(); do not use parent initJs to deactivate the default menuItem js
        return $this->consoleItem->getUniqId();
    }
}