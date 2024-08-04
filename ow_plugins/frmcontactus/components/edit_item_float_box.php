<?php
class FRMCONTACTUS_CMP_EditItemFloatBox extends OW_Component
{
    public function __construct($id)
    {
        parent::__construct();
        $form = FRMCONTACTUS_BOL_Service::getInstance()->getDepartmentEditForm($id);
        $this->addForm($form);
    }
}
