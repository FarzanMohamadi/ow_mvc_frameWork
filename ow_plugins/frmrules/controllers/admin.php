<?php
class FRMRULES_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index($params)
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmrules', 'admin_page_heading'));
        $this->setPageTitle($language->text('frmrules', 'admin_page_title'));
        $service = $this->getService();
        $sectionId = $service->getGuideLineSectionName();
        if(!isset($params['sectionId'])){
            $this->redirect(OW::getRouter()->urlForRoute('frmrules.admin.section-id', array('sectionId' => $service->getGuideLineSectionName())));
        }else {
            $sectionId = $params['sectionId'];

            $this->assign('sectionId', $sectionId);
            $this->addComponent('sections', $service->getSections($sectionId));

            if ($sectionId != $service->getGuideLineSectionName()) {
                $allItems = $service->getAllItems($sectionId);
                $allCategories = $service->getAllCategories($sectionId);

                $items = array();
                $number = 1;
                foreach ($allItems as $item) {
                    $itemInformation = array(
                        'id' => $item->id,
                        'name' => $item->name,
                        'number' => $number,
                        'description' => $item->description,
                        'tag' => $item->tag,
                        'deleteUrl' => "javascript:deleteRuleItem('" . OW::getRouter()->urlForRoute('frmrules.admin.delete-item', array('id' => $item->id)) . "', '" . OW::getLanguage()->text('frmrules', 'delete_item_warning') . "')",
                        'editUrl' => "javascript:editRuleItem('" . $item->id . "', '" . OW::getLanguage()->text('frmrules', 'edit_item_page_title') . "')"
                    );
                    if (!empty($item->icon)) {
                        $itemInformation['icon'] = $service->getIconUrl($item->icon);
                    }
                    $category = $service->getCategory($item->categoryId);
                    if (!empty($category->icon)) {
                        $itemInformation['categoryIcon'] = $service->getIconUrl($category->icon);
                    }
                    $items[] = $itemInformation;
                    $number++;
                }
                $this->assign('items', $items);

                $categories = array();
                foreach ($allCategories as $category) {
                    $catInformation = array(
                        'id' => $category->id,
                        'name' => $category->name,
                        'deleteUrl' => "javascript:deleteRuleCategory('" . OW::getRouter()->urlForRoute('frmrules.admin.delete-category', array('id' => $category->id)) . "', '" . OW::getLanguage()->text('frmrules', 'delete_item_warning') . "')",
                        'editUrl' => "javascript:editRuleCategory('" . $category->id . "', '" . OW::getLanguage()->text('frmrules', 'edit_category_page_title') . "')"
                    );
                    if (!empty($category->icon)) {
                        $catInformation['icon'] = $service->getIconUrl($category->icon);
                    }
                    $categories[] = $catInformation;
                }
                $this->assign("categories", $categories);
                $this->assign('add_new_category_label', OW::getLanguage()->text('frmrules', 'add_new_category_label'));
                $this->assign('add_new_item_label', OW::getLanguage()->text('frmrules', 'add_new_item_label'));
                $this->assign('add_new_item_url', "OW.ajaxFloatBox('FRMRULES_CMP_AddItemFloatBox', {sectionId: " . $sectionId . "} , {iconClass: 'ow_ic_edit', title: '" . OW::getLanguage()->text('frmrules', 'add_item_page_title') . "'})");
                $this->assign('add_new_category_url', "OW.ajaxFloatBox('FRMRULES_CMP_AddCategoryFloatBox', {sectionId: " . $sectionId . "} , {iconClass: 'ow_ic_edit', title: '" . OW::getLanguage()->text('frmrules', 'add_category_page_title') . "'})");
                OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmrules')->getStaticJsUrl() . 'frmrules.js');
            } else {
                $form = new Form('frmrules_guidline_form');
                $config = OW::getConfig();
                $configs = $config->getValues('frmrules');
                $buttons = array(
                    BOL_TextFormatService::WS_BTN_BOLD,
                    BOL_TextFormatService::WS_BTN_ITALIC,
                    BOL_TextFormatService::WS_BTN_UNDERLINE,
                    BOL_TextFormatService::WS_BTN_IMAGE,
                    BOL_TextFormatService::WS_BTN_LINK,
                    BOL_TextFormatService::WS_BTN_ORDERED_LIST,
                    BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
                    BOL_TextFormatService::WS_BTN_MORE,
                    BOL_TextFormatService::WS_BTN_SWITCH_HTML,
                    BOL_TextFormatService::WS_BTN_HTML,
                    BOL_TextFormatService::WS_BTN_VIDEO
                );
                $guidLineField = new WysiwygTextarea('frmrules_guidline','frmrules', $buttons);
                $guidLineField->setLabel(OW::getLanguage()->text('frmrules','guidelineFieldLabel'));
                $guidLineField->setSize(WysiwygTextarea::SIZE_L);
                if(isset($configs['frmrules_guidline']) && $configs['frmrules_guidline']!=null) {
                    $guidLineField->setValue($configs['frmrules_guidline']);
                }
                $guidLineField->setRequired(true);
                $form->addElement($guidLineField);

                $submitFormData = new Submit('submit');
                $form->addElement($submitFormData);

                $this->addForm($form);
                if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
                    $data = $form->getValues();
                    $text = UTIL_HtmlTag::sanitize($data['frmrules_guidline']);
                    if ($config->configExists('frmrules', 'frmrules_guidline')) {
                        $config->saveConfig('frmrules', 'frmrules_guidline', $text);
                    }
                    $this->redirect();
                }
            }
        }
    }

    public function getService(){
        return FRMRULES_BOL_Service::getInstance();
    }

    public function deleteItem($params)
    {
        $item = $this->getService()->getItem($params['id']);
        $category = $this->getService()->getCategory($item->categoryId);
        $this->getService()->deleteItem($params['id']);
        OW::getFeedback()->info(OW::getLanguage()->text('frmrules', 'database_record_deleted'));
        $this->redirect( OW::getRouter()->urlForRoute('frmrules.admin.section-id', array('sectionId'=>$category->sectionId)) );
    }

    public function deleteCategory($params)
    {
        $category = $this->getService()->getCategory($params['id']);
        $this->getService()->deleteCategory($params['id']);
        OW::getFeedback()->info(OW::getLanguage()->text('frmrules', 'database_record_deleted'));
        $this->redirect( OW::getRouter()->urlForRoute('frmrules.admin.section-id', array('sectionId'=>$category->sectionId)) );
    }

    public function addItem($params)
    {
        $form = $this->getService()->getItemForm($params['sectionId'], null);
        if ( $form->isValid($_POST) ) {
            $this->getService()->saveItem($form->getElement('categoryId')->getValue(), $form->getElement('name')->getValue(),$form->getElement('description')->getValue(),$form->getElement('tag')->getValue(), $form->getElement('icon')->getValue());
            $category = FRMRULES_BOL_Service::getInstance()->getCategory($form->getElement('categoryId')->getValue());
            OW::getFeedback()->info(OW::getLanguage()->text('frmrules', 'database_record_add'));
            $this->redirect(OW::getRouter()->urlForRoute('frmrules.admin.section-id', array('sectionId'=>$category->sectionId)));
        }else{
            $this->redirect(OW::getRouter()->urlForRoute('frmrules.admin'));
        }
    }

    public function addCategory($params)
    {
        $sectionId = $params['sectionId'];
        $form = $this->getService()->getCategoryForm(null, $sectionId);
        if ( $form->isValid($_POST) ) {
            $this->getService()->saveCategory($form->getElement('name')->getValue(), $form->getElement('icon')->getValue(), $sectionId);
            OW::getFeedback()->info(OW::getLanguage()->text('frmrules', 'database_record_add'));
        }
        $this->redirect(OW::getRouter()->urlForRoute('frmrules.admin.section-id', array('sectionId' => $sectionId)));
    }

    public function editItem($params)
    {
        $item = $this->getService()->getItemById($params['id']);
        $category = $this->getService()->getCategory($item->categoryId);
        $form = $this->getService()->getItemForm($category->sectionId, null);
        if ( $form->isValid($_POST) ) {
            $item = $this->getService()->updateItem($params['id'], $form->getElement('categoryId')->getValue(), $form->getElement('name')->getValue(), $form->getElement('description')->getValue(), $form->getElement('tag')->getValue(), $form->getElement('icon')->getValue());
            OW::getFeedback()->info(OW::getLanguage()->text('frmrules', 'database_record_edit'));
            $this->redirect(OW::getRouter()->urlForRoute('frmrules.admin.section-id', array('sectionId'=>$category->sectionId)));
        }else{
            $this->redirect(OW::getRouter()->urlForRoute('frmrules.admin'));
        }
    }

    public function editCategory($params)
    {
        $sectionId = $params['sectionId'];
        $form = $this->getService()->getCategoryForm(null, $sectionId);
        if ( $form->isValid($_POST) ) {
            $item = $this->getService()->updateCategory($params['id'], $form->getElement('name')->getValue(), $form->getElement('icon')->getValue());
            OW::getFeedback()->info(OW::getLanguage()->text('frmrules', 'database_record_edit'));
            $this->redirect(OW::getRouter()->urlForRoute('frmrules.admin.section-id', array('sectionId'=>$item->sectionId)));
        }else{
            $this->redirect(OW::getRouter()->urlForRoute('frmrules.admin'));
        }
    }

    public function ajaxSaveItemsOrder(){
        if ( !empty($_POST['items']) && is_array($_POST['items']) )
        {
            foreach ( $_POST['items'] as $index => $id )
            {
                $item = $this->getService()->getItemById($id);
                $item->order = $index + 1;
                $this->getService()->saveItemUsingObject($item);
            }
        }
    }

}