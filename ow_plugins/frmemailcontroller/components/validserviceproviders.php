<?php
class FRMEMAILCONTROLLER_CMP_Validserviceproviders extends OW_Component
{
    public function __construct()
    {
        parent::__construct();

        $this->assign('description', OW::getLanguage()->text('frmemailcontroller','valid_email_provider_information_description'));
        $validEmailProviders= json_decode(OW::getConfig()->getValue('frmemailcontroller', 'valid_email_services'));
        if(sizeof($validEmailProviders)>0) {
            $this->assign('validEmailProviders',$validEmailProviders);
        }
    }
}
