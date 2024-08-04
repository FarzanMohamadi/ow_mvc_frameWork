<?php
class FRMTICKETING_CMP_EditCategoryFloatBox extends OW_Component
{
    public function __construct($id)
    {
        parent::__construct();
        $form = FRMTICKETING_BOL_TicketCategoryService::getInstance()->getItemForm($id);
        $this->addForm($form);
    }
}
