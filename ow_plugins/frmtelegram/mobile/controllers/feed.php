<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */
class FRMTELEGRAM_MCTRL_Feed extends OW_MobileActionController
{
    public function index($params)
    {
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmtelegram','main_menu_item'));
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

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'frmtelegram', 'mobile_main_menu_item');

        $this->setPageHeading(OW::getLanguage()->text('frmtelegram', 'list_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_write');

        if ( false && !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('frmtelegram', 'view') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('frmtelegram', 'view');
            throw new AuthorizationException($status['msg']);
        }

        $delete_isAuthorized = OW::getUser()->isAdmin();
        $this->assign('delete_isAuthorized', $delete_isAuthorized);

        $contentMenu = new BASE_MCMP_ContentMenu($service->getContentMenuItems());
        $contentMenu->setItemActive($selectedChat->id);
        $this->addComponent('menu', $contentMenu );
        $this->assign('chatType', $selectedChat->type);

        $count = (int) OW::getConfig()->getValue('frmtelegram', 'results_per_page');
        list($list, $itemsCount) = $this->getData($selectedChat, 0, $count);

        $entries = array();
        $firstId = $list[0]['dto']->id;
        $lastId = $list[0]['dto']->id;
        foreach ($list as $item) {
            $dto = $item['dto'];
            $new_item = array(
                "id" => $dto->id,
                "authorName" => $item['displayName'],
                "authorURL" => $item['url'],
                "authorPhotoSrc" => $item['authorPhotoSrc'],
                "entryId" => $dto->entryId,
                "authorView" => $this->getAuthorViewForItem($dto->authorName,$dto->entryId, $dto->chatId),
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
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmtelegram')->getStaticCssUrl() . 'frmtelegram.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("frmtelegram")->getStaticJsUrl() . "frmtelegram.js");
        $loadMoreUrl = OW::getRouter()->urlForRoute('frmtelegram.load.more', array('chatId'=>$selectedChat->id, 'id' => ''));
        $loadOlderUrl = OW::getRouter()->urlForRoute('frmtelegram.load.older', array('chatId'=>$selectedChat->id, 'id' => ''));
        OW::getDocument()->addOnloadScript('loadDynamicData("'.$loadMoreUrl.'","'.$loadOlderUrl.'","'.$firstId.'","'.$lastId.'","");');
        $this->assign('preloader_img_url' , OW::getThemeManager()->getThemeImagesUrl() . 'ajax_preloader_content.gif');
        $this->assign('new_items_img_url' , OW::getThemeManager()->getThemeImagesUrl() . 'photo_view_context.png');

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

    public function getAuthorViewForItem($author,$entryId,$chatroomId){
        $chatType = FRMTELEGRAM_BOL_Service::getInstance()->getChatTypeByChatroomId($chatroomId);
        if($chatType == "channel")
            return "";
        if($entryId==0){
            $bol_user = BOL_UserService::getInstance()->findByUsername($author);
            if($bol_user) {
                $displayName = BOL_UserService::getInstance()->getDisplayName($bol_user->getId());
                $url = BOL_UserService::getInstance()->getUserUrl($bol_user->getId());
                return '<a href="'.$url.'" class="telegram_item_author">'.$displayName.'</a>';
            }
        }
        if($author == "")
            return "";
        return '<span class="telegram_item_author">'.$author.'</span>';
    }

    public function deleteItem($params){
        $delete_isAuthorized = OW::getUser()->isAdmin();
        if(!$delete_isAuthorized)
            return;
        $id = $params['id'];

        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $service->deleteItemById($id);
        exit(json_encode(array('result' => 'ok')));
    }

    public function loadMore($params){
        $delete_isAuthorized = OW::getUser()->isAdmin();
        $chatroomId = $params['chatId'];
        $lastId = false;
        if(isset($params['id']))
            $lastId = $params['id'];
        $count = (int) OW::getConfig()->getValue('frmtelegram', 'results_per_page');

        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $chatType = FRMTELEGRAM_BOL_Service::getInstance()->getChatTypeByChatroomId($chatroomId);

        $list = $service->findListById($chatroomId,false,$lastId,$count);
        $results = array();
        foreach($list as $item) {
            $del = '';
            if($delete_isAuthorized)
                $del = '<a href="javascript://" onclick="delete_item(\''.OW::getRouter()->urlForRoute('frmtelegram.item.delete', array('id' => '')).'\','
                    .$item->id.')"> '.OW::getLanguage()->text('frmtelegram','remove').' </a>';
            $author_view = '';
            list($author_display,$author_url,$authorPhotoSrc) = $this->getAuthorViewInfo($item, $chatType);
            if($chatType!="channel"){
                $author_view = '
                <div class="owm_newsfeed_header_pic">
                    <div class="owm_avatar">
                        <a '.$author_url.'>
                            <img alt="'.$author_display.'" src="'.$authorPhotoSrc.'">
                        </a>
                    </div>
                </div>
                <div class="owm_newsfeed_header_cont">
                    <div class="owm_newsfeed_header_txt">
                        <a '.$author_url.'><b>'.$author_display.'</b></a>
                    </div>
                </div>';
            }
            $html_content = '
            <div id="items_wrapper">
                <div class="owm_newsfeed_item_cont ">
                    <div class="owm_newsfeed_context_menu">
                        <div class="owm_newsfeed_date">
                            <span> '
                .OW::getLanguage()->text('frmtelegram','at').' '.UTIL_DateTime::formatSimpleDate($item->timestamp).'</span>'.$del.'
                        </div>
                    </div>
                    <div class="owm_newsfeed_header clearfix">
                        '.$author_view.'
                    </div>
                    <div class="owm_newsfeed_body">
                        <div class="owm_newsfeed_body_status">'.$item->entry.'</div>
                    </div>
                </div>';
            array_unshift($results, $html_content );
        }
        if(count($results)>0)
            $lastId = $list[0]->id;
        exit(json_encode(array('lastId'=>$lastId, 'results' => $results)));
    }

    public function loadOlder($params){
        $chatroomId = $params['chatId'];
        $firstId = false;
        if(isset($params['id']))
            $firstId = $params['id'];
        $count = (int) OW::getConfig()->getValue('frmtelegram', 'results_per_page');

        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $chatType = FRMTELEGRAM_BOL_Service::getInstance()->getChatTypeByChatroomId($chatroomId);

        $delete_isAuthorized = OW::getUser()->isAdmin();
        $list = $service->findListById($chatroomId,$firstId,false,$count);
        $results = array();
        foreach($list as $item) {
            $del = '';
            if($delete_isAuthorized)
                $del = '<a href="javascript://" onclick="delete_item(\''.OW::getRouter()->urlForRoute('frmtelegram.item.delete', array('id' => '')).'\','
                    .$item->id.')"> '.OW::getLanguage()->text('frmtelegram','remove').' </a>';
            $author_view = '';
            list($author_display,$author_url,$authorPhotoSrc) = $this->getAuthorViewInfo($item, $chatType);
            if($chatType!="channel"){
                $author_view = '
                <div class="owm_newsfeed_header_pic">
                    <div class="owm_avatar">
                        <a '.$author_url.'>
                            <img alt="'.$author_display.'" src="'.$authorPhotoSrc.'">
                        </a>
                    </div>
                </div>
                <div class="owm_newsfeed_header_cont">
                    <div class="owm_newsfeed_header_txt">
                        <a '.$author_url.'><b>'.$author_display.'</b></a>
                    </div>
                </div>';
            }
            $html_content = '
            <div id="items_wrapper">
                <div class="owm_newsfeed_item_cont ">
                    <div class="owm_newsfeed_context_menu">
                        <div class="owm_newsfeed_date">
                            <span> '
                .OW::getLanguage()->text('frmtelegram','at').' '.UTIL_DateTime::formatSimpleDate($item->timestamp).'</span>'.$del.'
                        </div>
                    </div>
                    <div class="owm_newsfeed_header clearfix">
                        '.$author_view.'
                    </div>
                    <div class="owm_newsfeed_body">
                        <div class="owm_newsfeed_body_status">'.$item->entry.'</div>
                    </div>
                </div>';

            $results[] = $html_content;
        }
        if(count($results)>0)
            $firstId = $list[count($results)-1]->id;
        exit(json_encode(array('firstId'=>$firstId, 'results' => $results)));
    }

    private function getData( $chatroom, $first, $count )
    {
        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $chatType = FRMTELEGRAM_BOL_Service::getInstance()->getChatTypeByChatId($chatroom->chatId);

        $list = array();
        {
            OW::getDocument()->setTitle(OW::getLanguage()->text('frmtelegram', 'telegram_page_title'));
            OW::getDocument()->setDescription( OW::getLanguage()->text('frmtelegram', 'telegram_page_desc'));
            $arr = $service->findList($chatroom->id, $first, $count);
            foreach ( $arr as $item )
            {
                list($author,$url,$authorPhotoSrc) = $this->getAuthorViewInfo($item, $chatType);
                array_unshift($list, array('dto' => $item,
                    'displayName' => $author, 'url' => $url, 'authorPhotoSrc' => $authorPhotoSrc));
            }
            $itemsCount = $service->countEntries($chatroom->chatId);
        }

        return array($list, $itemsCount);
    }

    public function getAuthorViewInfo($item, $chatType){
        $author = $item->authorName;
        $authorPhotoSrc = $url = "";
        if($chatType != "channel") {
            $entryId = $item->entryId;
            if ($entryId == 0) {
                $bol_user = BOL_UserService::getInstance()->findByUsername($author);
                if ($bol_user) {
                    $author = BOL_UserService::getInstance()->getDisplayName($bol_user->getId());
                    $url = ' href="'.BOL_UserService::getInstance()->getUserUrl($bol_user->getId()).'" ';
                    $authorPhotoSrc = BOL_AvatarService::getInstance()->getAvatarUrl($bol_user->getId());
                }
            }
            else
            {
                $authorPhotoSrc = OW::getPluginManager()->getPlugin('frmtelegram')->getStaticCssUrl() . 'ic_telegram_blue.svg';
            }
        }
        return array($author,$url,$authorPhotoSrc);
    }
}