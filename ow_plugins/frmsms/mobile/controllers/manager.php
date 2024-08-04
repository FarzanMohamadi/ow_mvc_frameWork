<?php
class FRMSMS_MCTRL_manager extends OW_MobileActionController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function checkCode()
    {
        $service = FRMSMS_BOL_Service::getInstance();
        $service->checkJoinCodeController($this);
    }

    public function block()
    {
        $service = FRMSMS_BOL_Service::getInstance();
        $service->blockPageController($this);
    }

    public function resendToken(){
        $service = FRMSMS_BOL_Service::getInstance();
        $service->resendTokenController($this);
    }

    public function removeUnverifiedNumber(){
        $service = FRMSMS_BOL_Service::getInstance();
        $service->removeUnverifiedNumberController($this);
    }
}
