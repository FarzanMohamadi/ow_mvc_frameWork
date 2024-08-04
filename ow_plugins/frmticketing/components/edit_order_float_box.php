<?php
class FRMTICKETING_CMP_EditOrderFloatBox extends OW_Component
{
    public function __construct($id)
    {
        parent::__construct();
        $form = FRMTICKETING_BOL_TicketOrderService::getInstance()->getItemForm($id);
        $this->addForm($form);
    }
}
