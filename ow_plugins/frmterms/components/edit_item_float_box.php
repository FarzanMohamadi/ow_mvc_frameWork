<?php
class FRMTERMS_CMP_EditItemFloatBox extends OW_Component
{
    public function __construct($id)
    {
        parent::__construct();
        $form = FRMTERMS_BOL_Service::getInstance()->getItemForm($id,null);

        $this->addForm($form);
    }
}
