<?php
class FRMREPORT_BOL_Service {
    private static $classInstance;

    private $activityTypeDao;
    private $activationDao;
    private $reportDao;
    private $reportDetailDao;

    const REPORT_ENABLED_FOR_GROUP = "frmreport.report.enabled.for.group";
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->activityTypeDao = FRMREPORT_BOL_ActivityTypeDao::getInstance();
        $this->activationDao = FRMREPORT_BOL_ActivationDao::getInstance();

        $this->reportDao = FRMREPORT_BOL_ReportDao::getInstance();
        $this->reportDetailDao = FRMREPORT_BOL_ReportDetailDao::getInstance();
    }

    public function addActivityType($title){
        $activityType = $this->activityTypeDao->findActivityType($title);
        if(!isset($activityType)){
            $this->activityTypeDao->addActivityType($title);
            OW::getFeedback()->info($this->text('frmreport','activity_added_successfully'));
        }else{
            OW::getFeedback()->error($this->text('frmreport','title_is_exist_error'));
        }
    }
    public function deleteActivityType($id){
        if($this->isReportExistWithThisActivity($id)){
            OW::getFeedback()->error($this->text('frmreport','activity_exist_in_report_error'));
        }else{
            $this->activityTypeDao->deleteActivityType($id);
            OW::getFeedback()->info($this->text('frmreport','activity_deleted_successfully'));
        }
    }
    public function getActivityType($id){
       return $this->activityTypeDao->getActivityType($id);
    }
    public function findActivityType($title){
        return $this->activityTypeDao->findActivityType($title);
    }
    public function updateActivityType($id,$title){
        $this->activityTypeDao->updateActivityType($id,$title);
    }


    public function getActivityTypeList($first=null,$count=null){
        return $this->activityTypeDao->getActivityTypeList($first,$count);
    }
    public function getActivityListCount(){
        return $this->activityTypeDao->getActivityListCount();
    }
    public function hasActivityTypePermission()
    {
        return $this->activityTypeDao->hasActivityTypePermission();
    }

    public function enableReportWidget($groupId){
        return $this->activationDao->enableReportWidget($groupId);
    }
    public function disableReportWidget($groupId){
        return $this->activationDao->disableReportWidget($groupId);
    }
    public function isReportWidgetEnable($groupId)
    {
        return $this->activationDao->isReportWidgetEnable($groupId);
    }
    public function getGroupsWithActiveReportWidget(){
       return $this->activationDao->getGroupsWithActiveReportWidget();
    }
    public function getNumberOfGroupsWithActiveReportWidget(){
        return $this->activationDao->getNumberOfGroupsWithActiveReportWidget();
    }
    public function findNumberOfGroupsWithActiveReportWidget($title){
        return $this->activationDao->findNumberOfGroupsWithActiveReportWidget($title);
    }

    public function createReport($groupId,$year,$semester){
       $reportId= $this->reportDao->addReport($groupId,$year,$semester);
       return $reportId;
    }
    public function updateReport($reportId,$year,$semester){
        $this->reportDao->updateReport($reportId,$year,$semester);
    }
    public function deleteGroupReports($groupId){
        $reports = $this->reportDao->getAllReports($groupId);
        foreach($reports as $report){
            $reportId = $report->id;
            $this->deleteReport($reportId);
        }
    }
    public function deleteReport($reportId){
        $this->deleteReportDetails($reportId);
        $this->reportDao->deleteReport($reportId);
    }
    public function getReports($groupId,$first,$count){
        return $this->reportDao->getReports($groupId,$first,$count);
    }
    public function getNumberOfReports($groupId){
        return $this->reportDao->getNumberOfReports($groupId);
    }
    public function getSemesterReports($groupId,$year,$semester,$first,$count){
        return $this->reportDao->getSemesterReport($groupId,$year,$semester,$first,$count);
    }
    public function getNumberOfSemesterReport($groupId,$year,$semester){
        return $this->reportDao->getNumberOfSemesterReport($groupId,$year,$semester);
    }
    public function findReport($id){
        return $this->reportDao->findReport($id);
    }
    public function findGroupOfReport($reportId){
        return $this->reportDao->findGroupOfReport($reportId);
    }
    public function getOverallReports($first,$count){
        return $this->reportDao->getOverallReports($first,$count);
    }
    public function getOverallReportsOfGroup($groupId,$first,$count){
        return $this->reportDao->getOverallReportsOfGroup($groupId,$first,$count);
    }
    public function findOverallReportsOfGroup($title,$first,$count){
        return $this->reportDao->findOverallReportsOfGroup($title,$first,$count);
    }
    public function getDateOfLastReport($groupId){
        return $this->reportDao->getDateOfLastReport($groupId);
    }


    public function addReportDetail($reportId,$activityTypeId,$count,$description){
        $this->reportDetailDao->addDetail($reportId,$activityTypeId,$count,$description);
    }
    public function getReportDetails($reportId){
        return $this->reportDetailDao->getDetailsByReportId($reportId);
    }
    public function getReportItem($reportId,$activityTypeId){
        return $this->reportDetailDao->getReportItem($reportId,$activityTypeId);
    }
    public function hasReportItem($reportId,$activityTypeId){
        return $this->reportDetailDao->hasReportItem($reportId,$activityTypeId);
    }
    public function updateReportDetail($reportDetailId,$count,$description){
        $this->reportDetailDao->updateReportDetail($reportDetailId,$count,$description);
    }
    public function deleteReportDetail($reportDetailId){
        $this->reportDetailDao->deleteReportDetail($reportDetailId);

    }
    private function deleteReportDetails($reportId){
        $this->reportDetailDao->deleteReportDetails($reportId);
    }
    private function isReportExistWithThisActivity($activityTypeId){
        return $this->reportDetailDao->isReportExistWithThisActivity($activityTypeId);
    }


    public function canCreateReport($groupId){
        return $this->reportDao->canCreateReport($groupId);
    }
    public function canEditReport($reportId){
        return $this->reportDao->canEditReport($reportId);
    }
    public function canViewReport($reportId){
        return $this->reportDao->canViewReport($reportId);
    }
    public function canViewReportsOfGroup($groupId){
        return $this->reportDao->canViewReportsOfGroup($groupId);
    }
    public function canViewOverallReports()
    {
        return (OW::getUser()->isAuthorized('frmreport'));
    }
    public function hasConflictWithOtherReports($groupId,$reportId,$year,$semester){
        return $this->reportDao->hasConflictWithOtherReports($groupId,$reportId,$year,$semester);
    }

    public function onGroupCategoryElementAdded(OW_Event $event){
        $params = $event->getParams();
        $data = $event->getData();
        $attr = OW::getRequestHandler()->getHandlerAttributes();
        if($attr[OW_RequestHandler::ATTRS_KEY_ACTION]!="create" && $attr[OW_RequestHandler::ATTRS_KEY_ACTION]!="edit"){
            return;
        }
        if(isset($params['form'])){
            $form = $params['form'];
            $reportStatusField = new CheckboxField('reportEnableStatus');
            $reportStatusField->setLabel($this->text('frmreport','report_enable'));
            $reportStatusField->setDescription($this->text('frmreport','report_plugin_description'));
            if(isset($params['groupId'])){
                $groupId=$params['groupId'];
                $isWidgetEnable= FRMREPORT_BOL_ActivationDao::getInstance()->isReportWidgetEnable($groupId);
                if($isWidgetEnable){
                    $reportStatusField->setValue(true);
                }else{
                    $reportStatusField->setValue(false);
                }
            }
            $form->addElement($reportStatusField);
            $data['form'] = $form;
            $data['hasReportElement'] = true;
            $event->setData($data);
        }

    }
    public function onGroupCreated(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['groupId'])){
            $groupId=$params['groupId'];
            if(isset($params['reportEnableStatus'])){
                $reportEnableStatus = $params['reportEnableStatus'];
            }else{
                $reportEnableStatus=false;
            }
            if($reportEnableStatus){
                $this->activationDao->enableReportWidget($groupId);
            }else{
                $this->activationDao->disableReportWidget($groupId);
            }
        }
    }
    public function onGroupDeleted(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['groupId'])){
            $groupId=$params['groupId'];
            $this->deleteGroupReports($groupId);
        }
    }
    public function addReportWidget(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['controller']) && isset($params['groupId'])){
            $groupId = $params['groupId'];
            if($this->isReportWidgetEnable($groupId)){
                $bcw = new BASE_CLASS_WidgetParameter();
                $bcw->additionalParamList=array('entityId'=>$params['groupId']);
                $groupController = $params['controller'];
                $groupController->addComponent('groupReports', new FRMREPORT_MCMP_ReportsWidget($bcw));
                $reportBoxInformation = array(
                    'show_title' => true,
                    'title' => $this->text('frmreport','report_widget_title'),
                    'wrap_in_box' => true,
                    'icon' => 'ow_ic_info',
                    'type' => "",
                );
                $groupController->assign('reportBoxInformation', $reportBoxInformation);
            }
        }
    }

    public function getActivityTypeEditForm($id){
        $activityType = $this->getActivityType($id);

        $formName = 'edit-item';
        $actionRoute = OW::getRouter()->urlFor('FRMREPORT_CTRL_Admin', 'edit');

        $form = new Form($formName);
        $form->setAction($actionRoute);

        if ($activityType != null) {
            $idField = new HiddenField('id');
            $idField->setValue($activityType->id);
            $form->addElement($idField);
        }

        $fieldTitle = new TextField('title');
        $fieldTitle->setRequired();
        $fieldTitle->setInvitation(OW::getLanguage()->text('frmreport', 'activity_title'));
        $fieldTitle->setValue($activityType->title);
        $fieldTitle->setHasInvitation(true);
        $validator = new FRMREPORT_CLASS_TitleValidator();
        $language = OW::getLanguage();
        $validator->setErrorMessage($language->text('frmreport', 'title_error_already_exist'));
        $fieldTitle->addValidator($validator);
        $form->addElement($fieldTitle);

        $submit = new Submit('submit', 'button');
        $submit->setValue(OW::getLanguage()->text('frmreport', 'edit_activity'));
        $form->addElement($submit);
        return $form;
    }



    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }
}