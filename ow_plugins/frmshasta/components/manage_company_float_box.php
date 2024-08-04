<?php
class FRMSHASTA_CMP_ManageCompanyFloatBox extends OW_Component
{
    /**
     * FRMSHASTA_CMP_ManageCompanyFloatBox constructor.
     * @param $params
     * @throws Redirect404Exception
     */
    public function __construct($companyId)
    {
        if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAdmin()) {
            throw new Redirect404Exception();
        }

        parent::__construct();
        $service = FRMSHASTA_BOL_Service::getInstance();

        $this->addForm($service->getCreateCompanyForm($companyId));
        if($companyId!=null){
            $this->assign("deleteUrl",OW::getRouter()->urlForRoute('frmshasta_delete_company', array('id' => $companyId)));
        }
        FRMSHASTA_BOL_Service::getInstance()->addStaticFiles();
    }

}


