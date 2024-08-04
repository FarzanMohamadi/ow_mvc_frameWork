<?php
class FRMREPORT_CMP_ReportsWidget extends BASE_CLASS_Widget{
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

        $numberOfRecord = ( empty($params->customParamList['count']) ) ? 10 : (int) $params->customParamList['count'];
        $reports = $service->getReports($groupId,0,$numberOfRecord);

        $detailUrls = array();
        foreach ($reports as $report){
            $detailUrls[$report['id']] = OW::getRouter()->urlForRoute('report_detail',array('reportId' => $report['id']));
        }
        $this->assign('count',$count);
        $this->assign('view_report_list',OW::getRouter()->urlForRoute('report_index',array('groupId' => $groupId)));
        $this->assign('add_report',OW::getRouter()->urlForRoute('report_add',array('groupId' => $groupId)));
        $this->assign('reports',$reports);
        $this->assign('detailUrls',$detailUrls);
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
    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW_Language::getInstance()->text('frmreport', 'report_widget_settings_count'),
            'value' => 10
        );

        return $settingList;
    }

    private function setReportWidgetInvisible(){
        self::setSettingValue(self::SETTING_SHOW_TITLE,false);
        self::setSettingValue(self::SETTING_WRAP_IN_BOX,false);
    }

}