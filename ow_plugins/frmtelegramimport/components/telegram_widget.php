<?php
class FRMTELEGRAMIMPORT_CMP_TelegramWidget extends BASE_CLASS_Widget{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $service = FRMTELEGRAMIMPORT_BOL_Service::getInstance();
        $groupId = $params->additionalParamList['entityId'];
        $isWidgetEnable=$service->isWidgetEnable($groupId);
        $this->assign('visible',$isWidgetEnable);
        if(!$isWidgetEnable){
            $this->setTelegramtWidgetInvisible();
        }
        $plugin = OW::getPluginManager()->getPlugin('frmtelegramimport');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'frmtelegramimport.js');
        $this->assign("groupId", $groupId);
    }
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmtelegramimport', 'telegram_widget_title'),
            self::SETTING_ICON => self::ICON_FILE
        );
    }
    private function setTelegramtWidgetInvisible(){
        self::setSettingValue(self::SETTING_SHOW_TITLE,false);
        self::setSettingValue(self::SETTING_WRAP_IN_BOX,false);
    }
}