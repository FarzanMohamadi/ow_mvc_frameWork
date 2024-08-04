<?php
class FRMREPORT_CMP_EditActivityFloatBox extends OW_Component{
    public function __construct($id)
    {
        parent::__construct();
        $form = FRMREPORT_BOL_Service::getInstance()->getActivityTypeEditForm($id);
        $this->addForm($form);
    }
}