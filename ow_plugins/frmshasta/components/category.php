<?php
/**
 * component class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmshasta.classes
 * @since 1.0
 */
class FRMSHASTA_CMP_Category extends OW_Component
{
    /**
     * FRMSHASTA_CMP_Category constructor.
     * @param $params
     * @throws Redirect404Exception
     */
    public function __construct($params = array())
    {
        parent::__construct();
        $categoryId = $params['categoryId'];
        $service = FRMSHASTA_BOL_Service::getInstance();

        $files = $service->getCategoryFiles($categoryId, OW::getUser()->getId());
        $fileIds = array();
        $category = $service->getCategory($categoryId);

        $allFilesCategoryUrl = OW::getRouter()->urlForRoute('frmshasta_files');
        $allFilesCategoryUrl .= '?categoryId=' . $categoryId;

        foreach ($files as $file) {
            $fileIds[] = $file->id;
        }

         if ($service->hasUserAccessManager()) {
             $this->assign('manageAccess',true);
         }
        $categoryData = array(
            'id' => $category->id,
            'name' => $category->name,
            'url' => $allFilesCategoryUrl,
            'editUrl' => OW::getRouter()->urlForRoute('frmshasta_edit_category', array('id' => $category->id)),
            'deleteUrl' => OW::getRouter()->urlForRoute('frmshasta_delete_category', array('id' => $category->id)),
        );

        $this->addComponent('files', new FRMSHASTA_CMP_Files(array('fileIds' => $fileIds)));
        $this->assign('categoryInfo', $categoryData);
        FRMSHASTA_BOL_Service::getInstance()->addStaticFiles();
    }
}