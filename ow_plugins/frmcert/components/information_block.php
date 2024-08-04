<?php
/**
 * FRM Cert
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcert
 * @since 1.0
 */
class FRMCERT_CMP_InformationBlock extends OW_Component
{
    /**
     * Constructor.
     */
    public function __construct($items = array())
    {
        parent::__construct();
        $this->assign('data', $items );
    }
}
