<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmterms
 * @since 1.0
 */
class FRMRULES_CMP_AddItemFloatBox extends OW_Component
{
    /**
     * FRMRULES_CMP_AddItemFloatBox constructor.
     * @param null|string $sectionId
     */
    public function __construct($sectionId)
    {
        parent::__construct();
        $form = FRMRULES_BOL_Service::getInstance()->getItemForm($sectionId, OW::getRouter()->urlForRoute('frmrules.admin.add-item', array('sectionId' => $sectionId)));
        $this->addForm($form);
    }
}
