<?php
class FRMRULES_CMP_EditItemFloatBox extends OW_Component
{
    public function __construct($id)
    {
        parent::__construct();
        $item = FRMRULES_BOL_Service::getInstance()->getItem($id);
        $category = FRMRULES_BOL_Service::getInstance()->getCategory($item->categoryId);
        $form = FRMRULES_BOL_Service::getInstance()->getItemForm($category->sectionId, OW::getRouter()->urlForRoute('frmrules.admin.edit-item', array('id' => $id)), $item->name, $item->description, $item->icon, $item->categoryId, $item->tag);
        $this->addForm($form);
    }
}
