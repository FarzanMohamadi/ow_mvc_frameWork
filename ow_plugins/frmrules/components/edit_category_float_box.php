<?php
class FRMRULES_CMP_EditCategoryFloatBox extends OW_Component
{
    public function __construct($id)
    {
        parent::__construct();
        $category = FRMRULES_BOL_Service::getInstance()->getCategory($id);
        $form = FRMRULES_BOL_Service::getInstance()->getCategoryForm(OW::getRouter()->urlForRoute('frmrules.admin.edit-category', array('id' => $id)), $category->sectionId, $category->name, $category->icon);
        $this->addForm($form);
    }
}