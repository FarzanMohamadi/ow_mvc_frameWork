<?php
class FRMCHALLENGE_CMP_Challenge extends OW_Component
{
    public function __construct($challenges = array())
    {
        parent::__construct();
        $this->assign('challenges', $challenges);
    }
}
