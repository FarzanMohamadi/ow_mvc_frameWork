<?php
class FRMSHASTA_CMP_FileUploadFloatBox extends OW_Component
{
    public function __construct($fileId=null)
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }


        parent::__construct();
        $service = FRMSHASTA_BOL_Service::getInstance();

        $this->addForm($service->getFileForm($this,$fileId));
        FRMSHASTA_BOL_Service::getInstance()->addStaticFiles();
    }

}


