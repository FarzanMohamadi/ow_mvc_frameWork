<?php
//
//
//class FRMTECHNOLOGY_CMP_SupporterListWidget extends BASE_CLASS_Widget
//{
//
//
//    public function __construct( BASE_CLASS_WidgetParameter $params )
//    {
//        parent::__construct();
//
//        $technologyId = $params->additionalParamList['technologyId'];
//        $count = ( empty($params->customParamList['count']) ) ? 9 : (int) $params->customParamList['count'];
//
//        if ( $this->assignList($technologyId, $count) )
//        {
//            $this->setSettingValue(self::SETTING_TOOLBAR, array(array(
//                'label' => OW::getLanguage()->text('frmtechnology', 'widget_supporters_view_all'),
//                'href' => OW::getRouter()->urlForRoute('technology-supporter-list', array('technologyId' => $technologyId))
//            )));
//        }
//    }
//
//    private function assignList( $technologyId, $count )
//    {
//        $list = FRMTECHNOLOGY_BOL_Service::getInstance()->findSupporterList($technologyId, 0, $count);
//
//        $idlist = array();
//        foreach ( $list as $item )
//        {
//            $idlist[] = $item->id;
//        }
//
//        $data = array();
//
//        if ( !empty($idlist) )
//        {
//            $data = BOL_AvatarService::getInstance()->getDataForUserAvatars($idlist);
//        }
//
//        $this->assign("userIdList", $idlist);
//        $this->assign("data", $data);
//
//        return !empty($idlist);
//    }
//
//    public static function getSettingList()
//    {
//        $settingList = array();
//        $settingList['count'] = array(
//            'presentation' => self::PRESENTATION_NUMBER,
//            'label' => OW_Language::getInstance()->text('frmtechnology', 'widget_supporters_settings_count'),
//            'value' => 9
//        );
//
//        return $settingList;
//    }
//
//    public static function getStandardSettingValueList()
//    {
//        return array(
//            self::SETTING_SHOW_TITLE => true,
//            self::SETTING_WRAP_IN_BOX => true,
//            self::SETTING_TITLE => OW_Language::getInstance()->text('frmtechnology', 'widget_supporters_title'),
//            self::SETTING_ICON => self::ICON_USER
//        );
//    }
//
//    public static function getAccess()
//    {
//        return self::ACCESS_ALL;
//    }
//}