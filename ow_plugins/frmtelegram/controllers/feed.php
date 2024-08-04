<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */
class FRMTELEGRAM_CTRL_Feed extends OW_ActionController
{
    public function index($params)
    {
        $service = FRMTELEGRAM_BOL_Service::getInstance();
//        $service->getBotUpdates();
        
        $chatRooms = $service->getChatrooms(true);
        if ( count($chatRooms)==0 )
        {
            $showList = false;
            $this->assign('showList', $showList);
            return;
        }
        $showList = true;
        $this->assign('showList', $showList);

        $selectedChat = null;
        if ( empty($params['list']) ) {
            $selectedChat = $chatRooms[0];
        }
        else {
            $selectedChat = $service->getChatroom($params['list']);
            if (!in_array($selectedChat, $chatRooms)) {
                throw new Redirect404Exception();
            }
        }

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'frmtelegram', 'main_menu_item');

        $this->setPageHeading(OW::getLanguage()->text('frmtelegram', 'list_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_write');

        if ( false && !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('frmtelegram', 'view') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('frmtelegram', 'view');
            throw new AuthorizationException($status['msg']);
        }
        $delete_isAuthorized = OW::getUser()->isAdmin();
        $this->assign('delete_isAuthorized', $delete_isAuthorized);

        $contentMenu = new BASE_CMP_ContentMenu($service->getContentMenuItems());
        $contentMenu->setItemActive($selectedChat->id);
        $this->addComponent('menu', $contentMenu );
        $this->assign('listId', $selectedChat->id);
        $this->assign('chatDesc', $selectedChat->desc);

        $count = (int) OW::getConfig()->getValue('frmtelegram', 'results_per_page');
        list($list, $itemsCount) = $this->getData($selectedChat, 0, $count);

        $entries = array();
        $firstId = $list[0]['dto']->id;
        $lastId = $list[0]['dto']->id;
        foreach ($list as $item) {
            $dto = $item['dto'];
            $new_item = array(
                "id" => $dto->id,
                "authorName" => $dto->authorName,
                "entryId" => $dto->entryId,
                "authorView" => $service->getAuthorViewForItem($dto->authorName,$dto->entryId, $dto->chatId),
                "entry" => $dto->entry,
                "timestamp" => UTIL_DateTime::formatSimpleDate($dto->timestamp),
                "isFile" => false,
                "fileCaption" => ""
            );
            array_unshift($entries,$new_item);
            if($dto->id < $firstId)
                $firstId = $dto->id;
            if($dto->id > $lastId)
                $lastId = $dto->id;
        }
        $this->assign('list', $entries);
        $this->assign('delete_url',OW::getRouter()->urlForRoute('frmtelegram.item.delete', array('id' => '')));

        //--add ajax to load
        $jsDir = OW::getPluginManager()->getPlugin("frmtelegram")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "frmtelegram.js");
        $loadMoreUrl = OW::getRouter()->urlForRoute('frmtelegram.load.more', array('chatId'=>$selectedChat->id, 'id' => ''));
        $loadOlderUrl = OW::getRouter()->urlForRoute('frmtelegram.load.older', array('chatId'=>$selectedChat->id, 'id' => ''));
        OW::getDocument()->addOnloadScript('loadDynamicData("'.$loadMoreUrl.'","'.$loadOlderUrl.'","'.$firstId.'","'.$lastId.'","");');
        $this->assign('preloader_img_url' , OW::getThemeManager()->getThemeImagesUrl() . 'ajax_preloader_content.gif');
        $this->assign('new_items_img_url' , OW::getThemeManager()->getThemeImagesUrl() . 'ic_up_arrow.svg');

        //------add new
        $this->assign('addNew_isAuthorized', $service->isEligibleToPost($selectedChat->chatId));

        $form = new Form('sendTelegram');
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->setAction(OW::getRouter()->urlForRoute('frmtelegram.messages'));
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){item_added(data);}');

        $chatIdText = new HiddenField('chatId');
        $chatIdText->setValue($selectedChat->chatId);
        $form->addElement($chatIdText);

        $sendText = new Textarea('text');
        $sendText->setLabel(OW::getLanguage()->text('frmtelegram','send_text'));
        $sendText->setRequired();
        $sendText->addValidator(new StringValidator());
        $form->addElement($sendText);

        $submit = new Submit('save');
        $form->addElement($submit);
        $this->addForm($form);

        if ( OW::getRequest()->isAjax() )
        {
            if ( $form->isValid($_POST) )
            {
                $chatId = $form->getElement('chatId')->getValue();
                if(!$service->isEligibleToPost($chatId))
                    exit(json_encode(array('result' => 'false')));

                $sendText = $form->getElement('text')->getValue();
                if(UTIL_HtmlTag::stripTags($sendText)=="")
                    exit(json_encode(array('result' => 'false')));

                $res = $service->sendNewTextToBot($sendText,$chatId);

                exit(json_encode(array(
                    'result' => $res,
                    'message' => $sendText)
                ));
            }
        }
    }


    public function deleteItem($params){
        $delete_isAuthorized = OW::getUser()->isAdmin();
        if(!$delete_isAuthorized)
            exit(json_encode(array('result' => 'false')));
        $id = $params['id'];

        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $service->deleteItemById($id);
        exit(json_encode(array('result' => 'ok')));
    }

    public function loadMore($params){
        $delete_isAuthorized = OW::getUser()->isAdmin();
        $chatId = $params['chatId'];
        $lastId = false;
        if(isset($params['id']))
            $lastId = $params['id'];
        $count = (int) OW::getConfig()->getValue('frmtelegram', 'results_per_page');

        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $service->getBotUpdates();

        $list = $service->findListById($chatId,false,$lastId,$count);
        $results = array();
        foreach($list as $item) {
            $del = '';
            if($delete_isAuthorized)
                $del = '<a href="" onclick="delete_item(\''.OW::getRouter()->urlForRoute('frmtelegram.item.delete', array('id' => '')).'\','
                    .$item->id.')"> '.OW::getLanguage()->text('frmtelegram','remove').' </a>';

            $html_content = '
            <div class="item_wrapper ow_ipc ow_stdmargin clearfix" id="telegram_item_'.$item->id.'">
				'.$service->getAuthorViewForItem($item->authorName,$item->entryId, $item->chatId).'
				<div class="ow_ipc_info">
					<div class="ow_ipc_content" style="display: inline;">
						<span style="white-space: pre-wrap;">'.$item->entry.'</span>
					</div>
					<div class="clearfix">
						<div class="ow_ipc_toolbar ow_remark">
      		                <span class="ow_ipc_date">'
                .OW::getLanguage()->text('frmtelegram','at').' '.UTIL_DateTime::formatSimpleDate($item->timestamp).'
                            '.$del.'
							</span>
						</div>
					</div>
				</div>
			</div>';
            array_unshift($results, $html_content );
        }
        if(count($results)>0)
            $lastId = $list[0]->id;
        exit(json_encode(array('lastId'=>$lastId, 'results' => $results)));
    }

    public function loadOlder($params){
        $chatId = $params['chatId'];
        $firstId = false;
        if(isset($params['id']))
            $firstId = $params['id'];
        $count = (int) OW::getConfig()->getValue('frmtelegram', 'results_per_page');

        $service = FRMTELEGRAM_BOL_Service::getInstance();

        $delete_isAuthorized = OW::getUser()->isAdmin();
        $list = $service->findListById($chatId,$firstId,false,$count);
        $results = array();
        foreach($list as $item) {
            $del = '';
            if($delete_isAuthorized)
                $del = '<a href="" onclick="delete_item(\''.OW::getRouter()->urlForRoute('frmtelegram.item.delete', array('id' => '')).'\','
                .$item->id.')"> '.OW::getLanguage()->text('frmtelegram','remove').' </a>';

            $results[] = '
            <div class="item_wrapper ow_ipc ow_stdmargin clearfix" id="telegram_item_'.$item->id.'">
				'.$service->getAuthorViewForItem($item->authorName,$item->entryId, $item->chatId).'
				<div class="ow_ipc_info">
					<div class="ow_ipc_content" style="display: inline;">
						<span style="white-space: pre-wrap;">'.$item->entry.'</span>
					</div>
					<div class="clearfix">
						<div class="ow_ipc_toolbar ow_remark">
      		                <span class="ow_ipc_date">'
                    .OW::getLanguage()->text('frmtelegram','at').' '.UTIL_DateTime::formatSimpleDate($item->timestamp).'
                            '.$del.'
							</span>
						</div>
					</div>
				</div>
			</div>';
        }
        if(count($results)>0)
            $firstId = $list[count($results)-1]->id;
        exit(json_encode(array('firstId'=>$firstId, 'results' => $results)));
    }

    public function widgetLoadMore($params){
        $chatId = $params['chatId'];
        $lastId = false;
        if(isset($params['id']))
            $lastId = $params['id'];
        $count = (int) OW::getConfig()->getValue('frmtelegram', 'results_per_page');

        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $service->getBotUpdates();

        $list = $service->findListById($chatId,false,$lastId,$count);
        $results = array();
        foreach($list as $item) {
            $html_content = '
            <div class="item_wrapper ow_ipc ow_stdmargin clearfix" id="telegram_item_'.$item->id.'">
				'.$service->getAuthorViewForItem($item->authorName,$item->entryId, $item->chatId).'
				<div class="ow_ipc_info">
					<div class="ow_ipc_content" style="display: inline;">
						<span style="white-space: pre-wrap;">'.$item->entry.'</span>
					</div>
					<div class="clearfix">
						<div class="ow_ipc_toolbar ow_remark">
      		                <span class="ow_ipc_date">'
                .OW::getLanguage()->text('frmtelegram','at').' '.UTIL_DateTime::formatSimpleDate($item->timestamp).'
							</span>
						</div>
					</div>
				</div>
			</div>';
            array_unshift($results, $html_content );
        }
        if(count($results)>0)
            $lastId = $list[0]->id;
        exit(json_encode(array('lastId'=>$lastId, 'results' => $results)));
    }

    public function widgetLoadOlder($params){
        $chatId = $params['chatId'];
        $firstId = false;
        if(isset($params['id']))
            $firstId = $params['id'];
        $count = (int) OW::getConfig()->getValue('frmtelegram', 'results_per_page');

        $service = FRMTELEGRAM_BOL_Service::getInstance();

        $list = $service->findListById($chatId,$firstId,false,$count);
        $results = array();
        foreach($list as $item) {
            $results[] = '
            <div class="item_wrapper ow_ipc ow_stdmargin clearfix" id="telegram_item_'.$item->id.'">
				'.$service->getAuthorViewForItem($item->authorName,$item->entryId, $item->chatId).'
				<div class="ow_ipc_info">
					<div class="ow_ipc_content" style="display: inline;">
						<span style="white-space: pre-wrap;">'.$item->entry.'</span>
					</div>
					<div class="clearfix">
						<div class="ow_ipc_toolbar ow_remark">
      		                <span class="ow_ipc_date">'
                .OW::getLanguage()->text('frmtelegram','at').' '.UTIL_DateTime::formatSimpleDate($item->timestamp).'
							</span>
						</div>
					</div>
				</div>
			</div>';
        }
        if(count($results)>0)
            $firstId = $list[count($results)-1]->id;
        exit(json_encode(array('firstId'=>$firstId, 'results' => $results)));
    }

    private function getData( $chatroom, $first, $count )
    {
        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $list = array();
        {
            OW::getDocument()->setTitle(OW::getLanguage()->text('frmtelegram', 'telegram_page_title'));
            OW::getDocument()->setDescription( OW::getLanguage()->text('frmtelegram', 'telegram_page_desc'));
            $arr = $service->findList($chatroom->id, $first, $count);
            foreach ( $arr as $item )
            {
                array_unshift($list, array('dto' => $item) );
            }
            $itemsCount = $service->countEntries($chatroom->chatId);
        }

        return array($list, $itemsCount);
    }

}