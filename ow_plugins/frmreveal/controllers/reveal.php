<?php
class FRMREVEAL_CTRL_Reveal extends OW_ActionController
{

    public function index($params)
    {
        OW::getConfig()->saveConfig('frmreveal', 'already_loaded', false);

        $this->redirect(OW_URL_HOME);
    }

}