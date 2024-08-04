<?php
class FRMEVENTPLUS_CMP_EditItemFloatBox extends OW_Component
{
    public function __construct($id)
    {
        parent::__construct();
        $form = FRMEVENTPLUS_BOL_Service::getInstance()->getItemForm($id);
        $this->addForm($form);
    }
}
