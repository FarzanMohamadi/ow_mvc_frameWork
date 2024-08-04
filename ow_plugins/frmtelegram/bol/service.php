<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */
class FRMTELEGRAM_BOL_Service
{
    /**
     * @var FRMTELEGRAM_BOL_TelegramEntryDao
     */
    private $telegramDao;

    /**
     * @var FRMTELEGRAM_BOL_TelegramChatroomDao
     */
    private $telegramChatroomDao;


    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->telegramDao = FRMTELEGRAM_BOL_TelegramEntryDao::getInstance();
        $this->telegramChatroomDao = FRMTELEGRAM_BOL_TelegramChatroomDao::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var FRMTELEGRAM_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTELEGRAM_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getContentMenuItems()
    {
        $menuItems = array();
        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $chatRooms = $service->getChatrooms(true);
        foreach ( $chatRooms as $listKey => $listArr )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setKey($listArr->id);
            $menuItem->setUrl(OW::getRouter()->urlForRoute('frmtelegram.messages.list', array('list' => $listArr->id)));
            $menuItem->setLabel($listArr->title);
            if(!$listArr->title)
                $menuItem->setLabel(OW::getLanguage()->text('frmtelegram', 'chat_no_title'));
            $menuItem->setIconClass("ow_ic_comment");
            array_unshift($menuItems, $menuItem );
        }

        return $menuItems;
    }

    public function itemExists($entryId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('entryId', $entryId);

        return ($this->telegramDao->countByExample($ex)>0);
    }

    public function addItem($chatId, $chatTitle, $chatType, $entryId, $authorName, $text, $timestamp)
    {
        $chatRoom = $this->telegramChatroomDao->getItemByChatId($chatId);
        if(count($chatRoom)>0)
            $chatRoom = $chatRoom[0];
        else{
            $tRoom = new FRMTELEGRAM_BOL_TelegramChatroom();
            $tRoom->chatId = $chatId;
            $tRoom->title = UTIL_HtmlTag::stripTags($chatTitle);
            $tRoom->type = $chatType;
            $tRoom->visible = false;
            $tRoom->desc = "";
            $tRoom->orderN = 1000;
            $this->telegramChatroomDao->save($tRoom);
            $chatRoom = $this->telegramChatroomDao->getItemByChatId($chatId);
            if(count($chatRoom)>0)
                $chatRoom = $chatRoom[0];
        }

        $tEntry = new FRMTELEGRAM_BOL_TelegramEntry();
        $tEntry->chatId = $chatRoom->id;
        $tEntry->entryId = $entryId;
        $tEntry->authorName = UTIL_HtmlTag::stripTags( $authorName);
        $tEntry->entry = UTIL_HtmlTag::stripTags($text);
        $tEntry->timestamp = $timestamp;
        $tEntry->isDeleted = false;
        $tEntry->isFile = false;
        $tEntry->fileCaption = "";
        if($tEntry->entry!="") {
            $this->telegramDao->save($tEntry);
        }

        return $tEntry;
    }
    public function activateItem($id)
    {
        $item = $this->telegramChatroomDao->findById($id);
        $item->visible = true;
        $this->telegramChatroomDao->save($item);
        return $item;
    }
    public function deactivateItem($id)
    {
        $item = $this->telegramChatroomDao->findById($id);
        $item->visible = false;
        $this->telegramChatroomDao->save($item);
        return $item;
    }
    public function deleteItemById($id)
    {
        $item = $this->telegramDao->findById($id);
        $item->isDeleted = true;
        $this->telegramDao->save($item);
        return $item;
    }
    public function countEntries($chatId)
    {
        return $this->telegramDao->countEntries($chatId);
    }

    public function findList($chatId, $first, $count )
    {
        $chatRoom = $this->telegramChatroomDao->findById($chatId);
        if(!$chatRoom)
            return [];

        if ($first < 0)
            $first = 0;

        if ($count < 1)
            $count = 1;

        $ex = new OW_Example();
        $ex->andFieldEqual('chatId', $chatRoom->id);
        $ex->andFieldNotEqual('isDeleted', true);
        $ex->setOrder('timestamp desc')->setLimitClause($first, $count);

        return $this->telegramDao->findListByExample($ex);
    }

    public function findListById($chatId,$firstId,$lastId,$count)
    {
        $chatRoom = $this->telegramChatroomDao->findById(intval($chatId));
        if(!$chatRoom)
            return [];

        $ex = new OW_Example();
        $ex->andFieldEqual('chatId', $chatRoom->id);
        $ex->andFieldNotEqual('isDeleted', true);
        if($firstId!=false)
            $ex->andFieldLessThan('id', intval($firstId));
        if($lastId!=false)
            $ex->andFieldGreaterThan('id', intval($lastId));
        $ex->setOrder('timestamp desc')->setLimitClause(0, intval($count));

        return $this->telegramDao->findListByExample($ex);
    }

    public function getChatroom($id)
    {
        return $this->telegramChatroomDao->findById($id);
    }
    public function saveChatroom($item)
    {
        $this->telegramChatroomDao->save($item);
        return $item;
    }

    public function countChatrooms()
    {
        return $this->telegramChatroomDao->countChatrooms();
    }
    public function getChatrooms($onlyVisible=false )
    {
        return $this->telegramChatroomDao->findList($onlyVisible);
    }
    public function getChatTypeByChatroomId($chatroomId){
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $chatroomId);
        $chatItem = $this->telegramChatroomDao->findObjectByExample($ex);
        return $chatItem->type;
    }
    public function getChatTypeByChatId($chatId){
        $ex = new OW_Example();
        $ex->andFieldEqual('chatId', $chatId);
        $chatItem = $this->telegramChatroomDao->findObjectByExample($ex);
        return $chatItem->type;
    }

    //---------edit item
    public function getAdminEditItemForm($id)
    {
        $item = $this->telegramChatroomDao->findById($id);
        $formName = 'edit-item';
        $actionRoute = OW::getRouter()->urlFor('FRMTELEGRAM_CTRL_Admin', 'editItem');

        $form = new Form($formName);
        $form->setAction($actionRoute);

        $idField = new HiddenField('id');
        $idField->setValue($item->id);
        $form->addElement($idField);

        $chatId = new TextField('chatId');
        $chatId->setLabel(OW::getLanguage()->text('frmtelegram', 'id_label'));
        $chatId->setRequired();
        $chatId->setValue($item->chatId);
        $form->addElement($chatId);

        $header = new TextField('title');
        $header->setRequired(true);
        $header->setLabel(OW::getLanguage()->text('frmtelegram', 'title_label'));
        $header->setHasInvitation(false);
        $header->setValue($item->title);
        $form->addElement($header);

        $descField = new Textarea('desc');
        $descField->setLabel(OW::getLanguage()->text('frmtelegram', 'desc_label'));
        $descField->setRequired();
        $descField->setValue($item->desc);
        $form->addElement($descField);

        $submit = new Submit('submit', 'button');
        $submit->setValue(OW::getLanguage()->text('frmtelegram', 'edit_item'));
        $form->addElement($submit);

        return $form;
    }
    public function editChatroomItem($id, $title, $desc=false, $chatId=false)
    {
        $item = $this->telegramChatroomDao->findById($id);
        if ($item == null) {
            return;
        }
        $item->title = $title;
        if($desc)
            $item->desc = $desc;
        if($chatId)
            $item->chatId = $chatId;
        $this->telegramChatroomDao->save($item);
        return $item;
    }

    //------
    public function isEligibleToPost($chatId){
        $chatType = FRMTELEGRAM_BOL_Service::getInstance()->getChatTypeByChatId($chatId);
        if($chatType == "channel")
            return  OW::getUser()->isAdmin();
        return  OW::getUser()->isAuthenticated();
    }

    public function getAuthorViewForItem($author,$entryId,$chatroomId){
        $chatType = FRMTELEGRAM_BOL_Service::getInstance()->getChatTypeByChatroomId($chatroomId);
        if($chatType == "channel")
            return "";
        if($entryId==0){
            $bol_user = BOL_UserService::getInstance()->findByUsername($author);
            if($bol_user) {
                $displayName = BOL_UserService::getInstance()->getDisplayName($bol_user->getId());
                $authorPhotoSrc = BOL_AvatarService::getInstance()->getAvatarUrl($bol_user->getId());
                $url = BOL_UserService::getInstance()->getUserUrl($bol_user->getId());
                return '<div class="ow_ipc_picture">
					<a href="'.$url.'"><img src="'.$authorPhotoSrc.'"
					 alt="'.$displayName.'" title="'.$displayName.'"></a>
				</div>';
            }
        }
        if($author == "")
            return "";
        return '<div class="ow_ipc_picture">
					<img src="'.OW::getPluginManager()->getPlugin('frmtelegram')->getStaticCssUrl() . 'ic_telegram_blue.svg'.
        '" alt="'.$author.'" title="'.$author.'">
				</div>';
    }

    public function sendNewTextToBot($sendText, $chatId){
        $userId = OW::getUser()->getId();
        $url = BOL_UserService::getInstance()->getUserUrl($userId);
        $userText = BOL_UserService::getInstance()->getDisplayName($userId) . " (".BOL_UserService::getInstance()->getUserName($userId).")";

        $bot_api_key = OW::getConfig()->getValue('frmtelegram', 'bot_api_key');
        $chatType = FRMTELEGRAM_BOL_Service::getInstance()->getChatTypeByChatId($chatId);
        if($chatType == "channel")
            $text = UTIL_HtmlTag::stripTags($sendText);
        else
            $text = '<a href="'.$url.'">'.$userText . "</a>: ".UTIL_HtmlTag::stripTags($sendText);
        $text = str_replace("\r\n","%0A",$text);
        $send_url = "https://api.telegram.org/bot".$bot_api_key."/sendMessage?parse_mode=HTML&chat_id=".$chatId."&text=".$text;

        $result = OW::getStorage()->fileGetContent($send_url, true);

        $contents_json = json_decode($result, true);
        if($contents_json==null)
            $res = false;
        else if(!isset($contents_json["ok"]) || $contents_json["ok"]==null)
            $res = false;
        else {
            $res = $contents_json["ok"];
            $item = $this->addItem($chatId, '', '', 0, BOL_UserService::getInstance()->getUserName($userId), $sendText, time());
        }
        return $res;
    }

    public function getBotUpdates(){
        $time_last = 0;
        if ( !OW::getConfig()->configExists('frmtelegram', 'get_updates_time_last') ){
            OW::getConfig()->saveConfig('frmtelegram', 'get_updates_time_last', $time_last);
        }
        $time_last = OW::getConfig()->getValue('frmtelegram', 'get_updates_time_last');
        $time_now = time();
        if($time_now - $time_last < 10){
            return;
        }
        OW::getConfig()->saveConfig('frmtelegram', 'get_updates_time_last', $time_now);

        if ( !OW::getConfig()->configExists('frmtelegram', 'get_updates_offset') ){
            OW::getConfig()->saveConfig('frmtelegram', 'get_updates_offset', '');
        }
        $offset = OW::getConfig()->getValue('frmtelegram', 'get_updates_offset');

        if ( !OW::getConfig()->configExists('frmtelegram', 'bot_api_key') ){
            OW::getConfig()->saveConfig('frmtelegram', 'bot_api_key', '');
        }
        $bot_api_key = OW::getConfig()->getValue('frmtelegram', 'bot_api_key');

        $url = "https://api.telegram.org/bot".$bot_api_key."/getUpdates?offset=".$offset."&time=".time();
        if($bot_api_key!="....:...-...") {
            $contents = OW::getStorage()->fileGetContent($url, true);
            //echo $contents;

            $contents_json = json_decode($contents, true);
            //print_r($contents_json);
            
            if (isset($contents_json["ok"])  && $contents_json["ok"] == true && isset($contents_json["result"])) {
                foreach ($contents_json["result"] as $item) {
                    $itemId = $item["update_id"];
                    if ($offset < $itemId)
                        $offset = $itemId;
                    if ($this->itemExists($itemId))
                        continue;
                    if (array_key_exists("message", $item)) {//post
                        if (!array_key_exists("from", $item["message"]) || !array_key_exists("chat", $item["message"]))
                            continue;
                        $chatId = $item["message"]["chat"]["id"];
                        $chatTitle = array_key_exists("title",$item["message"]["chat"]) ? $item["message"]["chat"]["title"]:"";
                        $chatType = array_key_exists("type",$item["message"]["chat"]) ? $item["message"]["chat"]["type"]:"private";
                        if ($chatType == "private")
                            continue;
                        $authorName = array_key_exists("first_name",$item["message"]["from"]) ? $item["message"]["from"]["first_name"]:"";
                        $lastName = array_key_exists("last_name",$item["message"]["from"]) ? ' '.$item["message"]["from"]["last_name"]:"";
                        $authorName = $authorName.$lastName;
                        $timestamp = $item["message"]["date"];
                        if (array_key_exists("text", $item["message"])) {
                            $text = $item["message"]["text"];
                            $this->addItem($chatId, $chatTitle, $chatType, $itemId, $authorName, $text, $timestamp);
                        } else if (array_key_exists("photo", $item["message"])) {
                            //$photo = $item["message"]["photo"];
                            //$caption = $item["message"]["caption"];
                            //$service->addFile($chatId, $itemId, $authorName, $photo, $caption, $timestamp);
                        }
                    } else if (array_key_exists("channel_post", $item)) {
                        $chatId = $item["channel_post"]["chat"]["id"];
                        $chatTitle = array_key_exists("title",$item["channel_post"]["chat"]) ? $item["channel_post"]["chat"]["title"]:"";
                        $chatType = $item["channel_post"]["chat"]["type"];
                        if ($chatType == "private")
                            continue;
                        $authorName = "";
                        $timestamp = $item["channel_post"]["date"];
                        if (array_key_exists("text", $item["channel_post"])) {
                            $text = $item["channel_post"]["text"];
                            $this->addItem($chatId, $chatTitle, $chatType, $itemId, $authorName, $text, $timestamp);
                        } else if (array_key_exists("photo", $item["channel_post"])) {
                            //$photo = $item["channel_post"]["photo"];
                            //$caption = $item["channel_post"]["caption"];
                            //$service->addFile($chatId, $itemId, $authorName, $photo, $caption, $timestamp);
                        }
                    }
                }
            }
        }
        OW::getConfig()->saveConfig('frmtelegram', 'get_updates_offset', $offset);
    }
}