<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */
class FRMTELEGRAM_CMP_FeedWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $enabledListForThisWidget = array();
        $service = FRMTELEGRAM_BOL_Service::getInstance();
//        $service->getBotUpdates();
        $chatRooms = $service->getChatrooms(true);
        foreach($chatRooms as $c){
            if ( !isset($params->customParamList['chat_'.$c->id]) )
                $enabledListForThisWidget[] = $c->id;
            else if($params->customParamList['chat_'.$c->id])
                $enabledListForThisWidget[] = $c->id;
        }
        $this->assignList($params,$enabledListForThisWidget);
    }

    private function assignList($params,$enabledListForThisWidget)
    {
        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $AllChatRooms = $service->getChatrooms(true);
        $chatRooms = [];
        foreach($AllChatRooms as $chatroom) {
            if (in_array($chatroom->id, $enabledListForThisWidget))
                $chatRooms[] = $chatroom;
        }

        //--add ajax to load
        $jsDir = OW::getPluginManager()->getPlugin("frmtelegram")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "frmtelegram.js");

        $menuItems = self::getMenu($chatRooms);
        $this->addComponent('menu', new BASE_CMP_WidgetMenu($menuItems));
        foreach($chatRooms as $chatroom){
            list($firstId, $lastId ,$tmp1) = $this->getListOfItems($chatroom);
            $menuItems[$chatroom->id]['list'] = $tmp1;
            $loadMoreUrl = OW::getRouter()->urlForRoute('frmtelegram.widget.load.more', array('chatId'=>$chatroom->id, 'id' => ''));
            $loadOlderUrl = OW::getRouter()->urlForRoute('frmtelegram.widget.load.older', array('chatId'=>$chatroom->id, 'id' => ''));
            OW::getDocument()->addOnloadScript(';loadDynamicData("'.$loadMoreUrl.'","'.$loadOlderUrl.'","'.$firstId.'","'.$lastId.'","_'.$chatroom->id.'");');
        }
        $this->assign('items', $menuItems);

        $this->assign('preloader_img_url' , OW::getThemeManager()->getThemeImagesUrl() . 'ajax_preloader_content.gif');
        $this->assign('new_items_img_url' , OW::getThemeManager()->getThemeImagesUrl() . 'ic_up_arrow.svg');
    }
    private function getData( $chatroom, $first, $count )
    {
        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $list = array();
        $itemsCount = 0;
        {
            $arr = $service->findList($chatroom->id, $first, $count);
            foreach ( $arr as $item )
            {
                array_unshift($list, array('dto' => $item) );
            }
            $itemsCount = $service->countEntries($chatroom->chatId);
        }
        return array($list, $itemsCount);
    }

    private static function getMenu($chatRooms)
    {
        $menuItems = array();
        $active = true;
        foreach($chatRooms as $chatRoom) {
            $menuItems[$chatRoom->id] = array(
                'label' => $chatRoom->title,
                'id' => $chatRoom->id,
                'contId' => 'frmtelegram_list_'.$chatRoom->id,
                'type' => $chatRoom->type,
                'active' => $active
            );
            $active = false;
        }
        return  ($menuItems);
    }
    private function getListOfItems($selectedChat){
        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $list = array();
        list($list, $itemsCount) = $this->getData($selectedChat, 0, 10);
        $firstId = $list[0]['dto']->id;
        $lastId = $list[0]['dto']->id;

        $entries = array();
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
        return array($firstId, $lastId, $entries);
    }

    public static function getSettingList()
    {
        $settingList = array();
        $service = FRMTELEGRAM_BOL_Service::getInstance();
        $chatRooms = $service->getChatrooms(true);
        foreach($chatRooms as $c){
            $settingList['chat_'.$c->id] = array(
                'presentation' => self::PRESENTATION_CHECKBOX,
                'label' => $c->title,// OW::getLanguage()->text('groups', 'widget_groups_show_titles_setting'),
                'value' => true
            );
        }
        return $settingList;
    }
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmtelegram', 'main_menu_item'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}