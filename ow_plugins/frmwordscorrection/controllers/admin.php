<?php
class FRMWORDSCORRECTION_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        if ( OW::getRequest()->isAjax() )
        {
            return;
        }

        $lang = OW::getLanguage();

        $this->setPageHeading($lang->text('frmwordscorrection', 'admin_settings_title'));
        $this->setPageTitle($lang->text('frmwordscorrection', 'admin_settings_title'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    public function settings()
    {
        $this->assign('correctUrl', OW::getRouter()->urlForRoute('frmwordscorrection-admin-correct'));
    }

    public function correct()
    {
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAdmin()){
            throw new Redirect404Exception();
        }else{
            FRMWORDSCORRECTION_BOL_Service::getInstance()->correctAll();
        }

        OW::getFeedback()->info(OW::getLanguage()->text('frmwordscorrection', 'correct_words_success'));
        $this->redirect(OW::getRouter()->urlForRoute('frmwordscorrection-admin'));
    }
}
