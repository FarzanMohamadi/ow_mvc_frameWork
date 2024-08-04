<?php
class FRMTELEGRAM_CMP_EditItemFloatBox extends OW_Component
{
    public function __construct($id)
    {
        parent::__construct();
        $form = FRMTELEGRAM_BOL_Service::getInstance()->getAdminEditItemForm($id);

        $this->addForm($form);
    }
}
