<?php
class FRMSHASTA_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    /**
     * FRMSHASTA_CTRL_Admin constructor.
     * @throws AuthenticateException
     * @throws InterceptException
     */
    public function __construct()
    {
        parent::__construct();

        if ( OW::getRequest()->isAjax() )
        {
            return;
        }

        $lang = OW::getLanguage();

        $this->setPageHeading($lang->text('frmshasta', 'admin_settings_title'));
        $this->setPageTitle($lang->text('frmshasta', 'admin_settings_title'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    public function index(){
        $service = FRMSHASTA_BOL_Service::getInstance();
        $this->addForm($service->getManageFieldForm());
    }
}
