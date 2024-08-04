<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmterms
 * @since 1.0
 */
class FRMTERMS_CMP_AddItem extends OW_Component
{
    /***
     * FRMTERMS_CMP_AddItem constructor.
     * @param null|string $sectionId
     */
    public function __construct($sectionId)
    {
        parent::__construct();
        $form = FRMTERMS_BOL_Service::getInstance()->getItemForm(null,$sectionId);

        $this->addForm($form);
    }
}
