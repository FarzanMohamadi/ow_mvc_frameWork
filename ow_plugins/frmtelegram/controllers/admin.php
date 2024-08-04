<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */
class FRMTELEGRAM_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('frmtelegram', 'admin_telegram_settings_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    /**
     * Default action
     */
    public function index()
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmtelegram', 'admin_telegram_settings_heading'));

        $form = new Form("form");
        $configs = OW::getConfig()->getValues('frmtelegram');

        $iconTypeItems = array();
        $iconTypeItems[1] =  OW::getLanguage()->text('frmtelegram', 'icon_type_hidden');
        $iconTypeItems[2] =  OW::getLanguage()->text('frmtelegram', 'icon_type_link');
        $iconTypeItems[3] =  OW::getLanguage()->text('frmtelegram', 'icon_type_list');
        $iconType = new Selectbox('icon_type');
        $iconType->setLabel(OW::getLanguage()->text('frmtelegram', 'icon_type'));
        $iconType->setHasInvitation(false);
        $iconType->setOptions($iconTypeItems);
        $iconType->setRequired();
        $iconType->setValue($configs['icon_type']);
        $form->addElement($iconType);

        $textField = new TextField('link');
        $textField->setLabel(OW::getLanguage()->text('frmtelegram', 'admin_channel_link'))
            ->setValue($configs['link']);
        $form->addElement($textField);

        $textField = new TextField('results_per_page');
        $textField->setLabel(OW::getLanguage()->text('frmtelegram', 'results_per_page'))
            ->setValue($configs['results_per_page'])->addValidator(new IntValidator())->setRequired(true);
        $form->addElement($textField);

        $textField = new TextField('bot_api_key');
        $textField->setLabel(OW::getLanguage()->text('frmtelegram', 'bot_api_key'))
            ->setValue($configs['bot_api_key'])->setRequired(true);
        $form->addElement($textField);


        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('frmtelegram', 'save_btn_label'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            OW::getConfig()->saveConfig('frmtelegram', 'icon_type', $data['icon_type']);
            OW::getConfig()->saveConfig('frmtelegram', 'link', $data['link']);
            OW::getConfig()->saveConfig('frmtelegram', 'results_per_page', $data['results_per_page']);
            OW::getConfig()->saveConfig('frmtelegram', 'bot_api_key', $data['bot_api_key']);
            OW::getFeedback()->info(OW::getLanguage()->text('frmtelegram', 'admin_changed_success'));
        }

        $this->addForm($form);

        $allItems = $this->getService()->getChatrooms();
        $activeItems = array();
        $inactiveItems = array();
        foreach ( $allItems as $item )
        {
            if($item->visible){
                $activeItems[] = array(
                    'id' => $item->id,
                    'chatId' => $item->chatId,
                    'title' => $item->title,
                    'type' => $item->type,
                    'visible' => $item->visible,

                    'activateUrl' => OW::getRouter()->urlForRoute('frmtelegram.admin.activate-item', array('id'=>$item->id)),
                    'deactivateUrl' => OW::getRouter()->urlForRoute('frmtelegram.admin.deactivate-item', array('id'=>$item->id)),
                    'editUrl' => "OW.ajaxFloatBox('FRMTELEGRAM_CMP_EditItemFloatBox', {id: ".$item->id."} , {iconClass: 'ow_ic_edit', title: '".OW::getLanguage()->text('frmtelegram', 'edit_item')."'})"
                );
            }else{
                $inactiveItems[] = array(
                    'id' => $item->id,
                    'chatId' => $item->chatId,
                    'title' => $item->title,
                    'type' => $item->type,
                    'visible' => $item->visible,

                    'activateUrl' => OW::getRouter()->urlForRoute('frmtelegram.admin.activate-item', array('id'=>$item->id)),
                    'deactivateUrl' => OW::getRouter()->urlForRoute('frmtelegram.admin.deactivate-item', array('id'=>$item->id)),
                    'editUrl' => "OW.ajaxFloatBox('FRMTELEGRAM_CMP_EditItemFloatBox', {id: ".$item->id."} , {iconClass: 'ow_ic_edit', title: '".OW::getLanguage()->text('frmtelegram', 'edit_item')."'})"
                );
            }
        }
        $this->assign('activeItems',$activeItems);
        $this->assign('inactiveItems',$inactiveItems);


        $cssDir = OW::getPluginManager()->getPlugin("frmtelegram")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "save-ajax-order-item.css");
    }
    public function getService(){
        return FRMTELEGRAM_BOL_Service::getInstance();
    }
    public function deactivateItem($params)
    {
        $item = $this->getService()->deactivateItem($params['id']);
        OW::getFeedback()->info(OW::getLanguage()->text('frmtelegram', 'database_record_deactivate_item'));
        $this->redirect( OW::getRouter()->urlForRoute('frmtelegram.admin') );
    }

    public function activateItem($params)
    {
        $item = $this->getService()->activateItem($params['id']);
        OW::getFeedback()->info(OW::getLanguage()->text('frmtelegram', 'database_record_activate_item'));
        $this->redirect( OW::getRouter()->urlForRoute('frmtelegram.admin') );
    }
    public function ajaxSaveOrder(){
        if ( !empty($_POST['active']) && is_array($_POST['active']) )
        {
            foreach ( $_POST['active'] as $index => $id )
            {
                $item = $this->getService()->getChatroom($id);
                $item->orderN = $index + 1;
                $item->visible = true;
                $this->getService()->saveChatroom($item);
            }
        }

        if ( !empty($_POST['inactive']) && is_array($_POST['inactive']) )
        {
            foreach ( $_POST['inactive'] as $index => $id )
            {
                $item = $this->getService()->getChatroom($id);
                $item->orderN = $index + 1;
                $item->visible = false;
                $this->getService()->saveChatroom($item);
            }
        }
        exit(json_encode(array('result'=>'ok')));
    }

    public function editItem()
    {
        $form = $this->getService()->getAdminEditItemForm($_POST['id']);
        if ( $form->isValid($_POST) ) {
            $item = $this->getService()->editChatroomItem($form->getElement('id')->getValue()
                , $form->getElement('title')->getValue()
                , $form->getElement('desc')->getValue()
                , $form->getElement('chatId')->getValue());

            OW::getFeedback()->info(OW::getLanguage()->text('frmtelegram', 'admin_changed_success'));
            $this->redirect(OW::getRouter()->urlForRoute('frmtelegram.admin'));
        }else{
            $this->redirect(OW::getRouter()->urlForRoute('frmtelegram.admin'));
        }
    }

}