<?php
class FRMGMAILCONNECT_MCMP_ConnectButton extends OW_MobileComponent
{

  public function render()
    {
     $this->assign('url',FRMGMAILCONNECT_BOL_Service::getInstance()->generateOAuthUri());
     return parent::render();
    }
}