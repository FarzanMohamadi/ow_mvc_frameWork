<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmvitrin.bol
 * @since 1.0
 */
class FRMVITRIN_BOL_Service
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $itemDao;

    private function __construct()
    {
        $this->itemDao = FRMVITRIN_BOL_ItemDao::getInstance();
    }

    /***
     * @param $itemId
     * @return FRMVITRIN_BOL_Item
     */
    public function getItem($itemId){
        return $this->itemDao->getItem($itemId);
    }

    /***
     * @return array
     */
    public function getItems(){
        return $this->itemDao->getItems();
    }

    /***
     * @param $title
     * @param $description
     * @param $logo
     * @param $businessModel
     * @param $language
     * @param $url
     * @param $targetMarket
     * @param $vendor
     * @return FRMVITRIN_BOL_Item|null
     */
    public function saveItem($title, $description, $logo, $businessModel, $language, $url, $targetMarket, $vendor){
        $order = $this->getMaxOrder() +1;
        return $this->itemDao->saveItem($title, $description, $order, $logo, $businessModel, $language, $url, $targetMarket, $vendor);
    }

    public function getMaxOrder(){
        return $this->itemDao->getMaxOrder();
    }

    /***
     * @param $itemId
     * @param $title
     * @param $description
     * @param $logo
     * @param $businessModel
     * @param $language
     * @param $url
     * @param $targetMarket
     * @param $vendor
     * @return mixed|null
     */
    public function update($itemId, $title, $description, $logo, $businessModel, $language, $url, $targetMarket, $vendor){
        return $this->itemDao->update($itemId, $title, $description, $logo, $businessModel, $language, $url, $targetMarket, $vendor);
    }

    /***
     * @param $action
     * @param null $descriptionValue
     * @return Form
     */
    public function getDescriptionForm($action, $descriptionValue = null){
        $form = new Form('descriptionForm');
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction($action);
        $form->bindJsFunction(Form::BIND_SUCCESS,'function( data ){ if(data && data.result){OW.info(\''.OW::getLanguage()->text('frmvitrin', 'saved_successfully').'\')  }  }');

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
            BOL_TextFormatService::WS_BTN_HTML
        );
        $description = new WysiwygTextarea('description','frmvitrin', $buttons);
        $description->setSize(WysiwygTextarea::SIZE_L);
        $description->setLabel(OW::getLanguage()->text('frmvitrin', 'description'));
        $description->setValue($descriptionValue);
        $description->setHasInvitation(false);
        $form->addElement($description);

        $submit = new Submit('save');
        $form->addElement($submit);

        return $form;
    }

    /***
     * @param $action
     * @param null $titleValue
     * @param null $descriptionValue
     * @param null $businessModelValue
     * @param null $languageValue
     * @param null $urlValue
     * @param null $targetMarketValue
     * @param null $vendorValue
     * @return Form
     */
    public function getItemForm($action, $titleValue = null, $descriptionValue = null, $businessModelValue = null, $languageValue = null, $urlValue = null, $targetMarketValue = null, $vendorValue = null){
        $form = new Form('item');
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $title = new TextField('title');
        $title->setRequired();
        $title->setLabel(OW::getLanguage()->text('frmvitrin', 'title'));
        $title->setValue($titleValue);
        $title->setHasInvitation(false);
        $form->addElement($title);

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
            BOL_TextFormatService::WS_BTN_HTML
        );
        $description = new WysiwygTextarea('description','frmvitrin', $buttons);
        $description->setSize(WysiwygTextarea::SIZE_L);
        $description->setLabel(OW::getLanguage()->text('frmvitrin', 'description'));
        $description->setRequired();
        $description->setValue($descriptionValue);
        $description->setHasInvitation(false);
        $form->addElement($description);

        $businessModel = new TextField('businessModel');
        $businessModel->setRequired();
        $businessModel->setLabel(OW::getLanguage()->text('frmvitrin', 'business_model'));
        $businessModel->setValue($businessModelValue);
        $businessModel->setRequired();
        $businessModel->setHasInvitation(false);
        $form->addElement($businessModel);

        $logo = new FileField('logo');
        $logo->setLabel(OW::getLanguage()->text('frmvitrin', 'logo'));
        $form->addElement($logo);

        $language = new TextField('language');
        $language->setRequired();
        $language->setLabel(OW::getLanguage()->text('frmvitrin', 'language'));
        $language->setValue($languageValue);
        $language->setRequired();
        $language->setHasInvitation(false);
        $form->addElement($language);

        $url = new TextField('url');
        $url->setRequired();
        $url->setLabel(OW::getLanguage()->text('frmvitrin', 'url'));
        $url->setValue($urlValue);
        $url->setRequired();
        $url->setHasInvitation(false);
        $form->addElement($url);

        $targetMarket = new TextField('targetMarket');
        $targetMarket->setRequired();
        $targetMarket->setLabel(OW::getLanguage()->text('frmvitrin', 'targetMarket'));
        $targetMarket->setValue($targetMarketValue);
        $targetMarket->setRequired();
        $targetMarket->setHasInvitation(false);
        $form->addElement($targetMarket);

        $vendor = new TextField('vendor');
        $vendor->setRequired();
        $vendor->setLabel(OW::getLanguage()->text('frmvitrin', 'vendor'));
        $vendor->setValue($vendorValue);
        $vendor->setRequired();
        $vendor->setHasInvitation(false);
        $form->addElement($vendor);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }

    /***
     * @param $name
     * @return string
     */
    public function getIconUrl($name){
        return OW::getPluginManager()->getPlugin('frmvitrin')->getStaticUrl().'/img/'.$name;
    }

    /***
     * @param $imageName
     * @return null|string
     */
    public function saveFile($imageName){
        if (!((int)$_FILES[$imageName]['error'] !== 0 || !is_uploaded_file($_FILES[$imageName]['tmp_name']) || !UTIL_File::validateImage($_FILES[$imageName]['name']))) {
            $logoName = FRMSecurityProvider::generateUniqueId() . '.' . UTIL_File::getExtension($_FILES[$imageName]['name']);
            $tmpImgPath = $this->getSaveFileDir($logoName);
            $image = new UTIL_Image($_FILES[$imageName]['tmp_name']);
            $image->saveImage($tmpImgPath);
            return $logoName;
        }

        return null;
    }

    /***
     * @param $logoName
     * @return string
     */
    public function getFileUrl($logoName){
        return OW::getPluginManager()->getPlugin('frmvitrin')->getUserFilesUrl() . $logoName;
    }

    /***
     * @param $logoName
     * @return string
     */
    public function getSaveFileDir($logoName){
        return OW::getPluginManager()->getPlugin('frmvitrin')->getUserFilesDir() . $logoName;
    }

    /***
     * @param $itemId
     */
    public function deleteItem($itemId){
        $this->itemDao->deleteById($itemId);
    }

    /***
     * @param $item
     */
    public function saveItemByObject($item){
        $this->itemDao->save($item);
    }
}
