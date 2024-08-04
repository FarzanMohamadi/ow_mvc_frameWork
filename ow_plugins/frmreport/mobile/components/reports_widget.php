<?php
class FRMREPORT_MCMP_ReportsWidget extends BASE_CLASS_Widget{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $service = FRMREPORT_BOL_Service::getInstance();
        $groupId = $params->additionalParamList['entityId'];
        $isEnable=$service->isReportWidgetEnable($groupId);
        $this->assign('visible',$isEnable);
        if(!$isEnable){
            $this->setReportWidgetInvisible();
        }
        $count = $service->getNumberOfReports($groupId);
        $this->assign('count',$count);
        $this->assign('view_report_list',OW::getRouter()->urlForRoute('report_index',array('groupId' => $groupId)));
        $this->assign('add_report',OW::getRouter()->urlForRoute('report_add',array('groupId' => $groupId)));

        $this->setTemplate(OW::getPluginManager()->getPlugin('frmreport')->getMobileCmpViewDir() . 'reports_widget.html');

    }
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmreport', 'report_widget_title'),
            self::SETTING_ICON => self::ICON_FILE
        );
    }

    private function setReportWidgetInvisible(){
        self::setSettingValue(self::SETTING_SHOW_TITLE,false);
        self::setSettingValue(self::SETTING_WRAP_IN_BOX,false);
    }
}