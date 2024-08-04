<?php
class FRMSHASTA_CMP_CategoryFloatBox extends OW_Component
{
    public function __construct($categoryId=null)
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }


        parent::__construct();
        $service = FRMSHASTA_BOL_Service::getInstance();
        if($categoryId!=null){
            $this->assign("deleteUrl",OW::getRouter()->urlForRoute('frmshasta_delete_category', array('id' => $categoryId)));
        }
        $this->addForm($service->getCategoryForm($this,$categoryId));
    }

}


