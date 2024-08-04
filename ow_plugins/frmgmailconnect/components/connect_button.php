<?php
class FRMGMAILCONNECT_CMP_ConnectButton extends OW_Component
{

  public function render()
    {
     $this->assign('url',FRMGMAILCONNECT_BOL_Service::getInstance()->generateOAuthUri());
     return parent::render();
    }
}