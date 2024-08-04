<?php
class FRMRECAPTCHA_CMP_InvisibleRecaptcha extends OW_Component
{
    public function __construct($siteKey)
    {
        parent::__construct();
        $this->assign('siteKey', $siteKey);

    }
}