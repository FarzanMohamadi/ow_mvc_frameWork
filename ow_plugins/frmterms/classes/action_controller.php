<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmterms.classes
 * @since 1.0
 */
class FRMTERMS_CLASS_ActionController extends OW_ActionController
{
    /**
     *
     * @var FRMTERMS_BOL_Service
     */
    protected $service;

    public function init()
    {
        $this->service = FRMTERMS_BOL_Service::getInstance();
    }
}

