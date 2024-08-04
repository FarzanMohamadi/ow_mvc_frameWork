<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmterms
 * @since 1.0
 */
class FRMRULES_CMP_AddCategoryFloatBox extends OW_Component
{
    /***
     * FRMRULES_CMP_AddCategoryFloatBox constructor.
     * @param null|string $sectionId
     */
    public function __construct($sectionId)
    {
        parent::__construct();
        $form = FRMRULES_BOL_Service::getInstance()->getCategoryForm(OW::getRouter()->urlForRoute('frmrules.admin.add-category', array('sectionId' => $sectionId)), $sectionId);
        $this->addForm($form);
    }
}
