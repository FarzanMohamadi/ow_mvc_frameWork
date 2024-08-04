<?php
class FRMTELEGRAMIMPORT_CMP_HelpFloatBox extends OW_Component{
    public function __construct($iconClass)
    {
        parent::__construct();
        $service =  FRMTELEGRAMIMPORT_BOL_Service::getInstance();
        $guideline = $service->getUserHelp();
        $this->assign('guideline',$guideline);
    }
}