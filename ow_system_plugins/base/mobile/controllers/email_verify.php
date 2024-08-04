<?php
/**
 * Email Verify controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.mobile.controller
 * @since 1.0
 */
class BASE_MCTRL_EmailVerify extends BASE_CTRL_EmailVerify
{
    protected function setMasterPage()
    {
         OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MobileMasterPage::TEMPLATE_BLANK));
    }

    public function index( $params )
    {
        parent::index($params);

        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir().'email_verify_index.html');
    }

    public function verify( $params )
    {
        parent::verify($params);

        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir().'email_verify_verify.html');
    }

    public function verifyForm( $params )
    {
        parent::verifyForm($params);
        
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MobileMasterPage::TEMPLATE_BLANK));
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCtrlViewDir().'email_verify_verify_form.html');
    }
}

