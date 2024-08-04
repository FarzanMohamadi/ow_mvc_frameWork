<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmvitrin.controllers
 * @since 1.0
 */
class FRMVITRIN_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index($params)
    {
        $this->setPageHeading(OW::getLanguage()->text('frmvitrin', 'admin_main_page_title'));
        $this->setPageTitle(OW::getLanguage()->text('frmvitrin', 'admin_main_page_title'));
        $service = FRMVITRIN_BOL_Service::getInstance();

        $itemForm = $service->getItemForm(OW::getRouter()->urlForRoute('frmvitrin.admin'));
        $this->addForm($itemForm);

        $descriptionForm = $service->getDescriptionForm(OW::getRouter()->urlForRoute('frmvitrin.admin'), OW::getConfig()->getValue('frmvitrin', 'description'));
        $this->addForm($descriptionForm);

        if (OW::getRequest()->isPost()) {
            if ($itemForm->isValid($_POST)) {
                $formData = $itemForm->getValues();
                $title = $formData['title'];
                $description = UTIL_HtmlTag::sanitize($formData['description']);
                $businessModel = $formData['businessModel'];
                $targetMarket = $formData['targetMarket'];
                $vendor = $formData['vendor'];
                $language = $formData['language'];
                $url = $formData['url'];
                try {
                    $logoName = $service->saveFile('logo');
                }catch(Exception $e){
                    $logoName = null;
                }
                if($logoName == null){
                    OW::getFeedback()->error(OW::getLanguage()->text('frmvitrin', 'empty_logo_error'));
                }else {
                    $service->saveItem($title, $description, $logoName, $businessModel, $language, $url, $targetMarket, $vendor);
                    OW::getFeedback()->info(OW::getLanguage()->text('frmvitrin', 'saved_successfully'));
                    $this->redirect();
                }
            }
        }

        if(OW::getRequest()->isAjax()){
            if ($descriptionForm->isValid($_POST)) {
                $formData = $descriptionForm->getValues();
                $description = UTIL_HtmlTag::sanitize($formData['description']);
                if(OW::getConfig()->configExists('frmvitrin','description')){
                    OW::getConfig()->saveConfig('frmvitrin','description',$description);
                }
                exit(json_encode(array('result' => true)));
            }
        }

        $items = $service->getItems();
        $itemsArray = array();
        foreach ($items as $item) {
            $itemsArray[] = array(
                'id'=> $item->id,
                'title'=> $item->title,
                'description' => $item->description,
                'language' => $item->language,
                'free' => $item->free,
                'url' => $item->url,
                'logo' => $service->getFileUrl($item->logo),
                'deleteUrl' => "if(confirm('".OW::getLanguage()->text('frmvitrin','delete_item_warning')."')){location.href='" . OW::getRouter()->urlForRoute('frmvitrin.admin.delete-item', array('id' => $item->id)) . "';}",
                'editUrl' => OW::getRouter()->urlForRoute('frmvitrin.admin.edit-item', array('id' => $item->id))
            );
        }

        $this->assign('items', $itemsArray);
        $cssDir = OW::getPluginManager()->getPlugin("frmvitrin")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "frmvitrin.css");
    }


    public function editItem($params)
    {
        if (!isset($params['id'])) {
            $this->redirect(OW::getRouter()->urlForRoute('frmvitrin.admin'));
        }
        $service = FRMVITRIN_BOL_Service::getInstance();
        $item = $service->getItem($params['id']);
        $itemForm = $service->getItemForm(OW::getRouter()->urlForRoute('frmvitrin.admin.edit-item', array('id' => $params['id'])), $item->title, $item->description, $item->businessModel, $item->language, $item->url, $item->targetMarket, $item->vendor);
        $this->addForm($itemForm);
        $this->assign('logoImageUrl', $service->getFileUrl($item->logo));

        $this->assign('returnToVitrin', OW::getRouter()->urlForRoute('frmvitrin.admin'));
        if (OW::getRequest()->isPost()) {
            if ($itemForm->isValid($_POST)) {
                $formData = $itemForm->getValues();
                $title = $formData['title'];
                $description = UTIL_HtmlTag::sanitize($formData['description']);
                $businessModel = $formData['businessModel'];
                $targetMarket = $formData['targetMarket'];
                $vendor = $formData['vendor'];
                $language = $formData['language'];
                $url = $formData['url'];
                try {
                    $logoName = $service->saveFile('logo');
                }catch(Exception $e){
                    $logoName = null;
                }
                if($logoName == null) {
                    $logoName = $item->logo;
                }
                $service->update($item->id, $title, $description, $logoName, $businessModel, $language, $url, $targetMarket, $vendor);
                OW::getFeedback()->info(OW::getLanguage()->text('frmvitrin', 'saved_successfully'));
                $this->redirect();
            }
        }

        $cssDir = OW::getPluginManager()->getPlugin("frmvitrin")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "frmvitrin.css");
    }

    public function deleteItem($params)
    {
        if (!isset($params['id'])) {
            $this->redirect(OW::getRouter()->urlForRoute('frmvitrin.admin'));
        }
        $service = FRMVITRIN_BOL_Service::getInstance();
        $service->deleteItem($params['id']);
        OW::getFeedback()->info(OW::getLanguage()->text('frmvitrin', 'deleted_successfully'));
        $this->redirect(OW::getRouter()->urlForRoute('frmvitrin.admin'));
    }

    public function ajaxSaveItemsOrder()
    {
        if (!empty($_POST['itemsId']) && is_array($_POST['itemsId'])) {
            $service = FRMVITRIN_BOL_Service::getInstance();
            foreach ($_POST['itemsId'] as $index => $id) {
                $item = $service->getItem($id);
                $item->order = $index + 1;
                $service->saveItemByObject($item);
            }
        }
    }
}