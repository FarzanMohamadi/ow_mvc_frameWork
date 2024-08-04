<?php
class FRMREPORT_BOL_ReportDao extends OW_BaseDao{
    protected function __construct()
    {
        parent::__construct();
    }

    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmreport_report';
    }

    public function getDtoClassName()
    {
        return 'FRMREPORT_BOL_Report';
    }

    public function addReport($groupId,$year,$semester){
        if($this->canCreateReport($groupId)){
            $report = new FRMREPORT_BOL_Report();

            $userId=OW::getUser()->getId();
            $timeStamp=time();

            $report->groupId = $groupId;
            $report->year=$year;
            $report->semester = $semester;
            $report->createDate = $timeStamp;
            $report->creator = $userId;
            $report->editDate = $timeStamp;
            $report->editor = $userId;
            $this->save($report);
            return $report->id;
        }
    }
    public function updateReport($reportId,$year,$semester){
        $report = $this->findById($reportId);
        if(isset($report)){
            $userId=OW::getUser()->getId();
            $timeStamp=time();
            $report->year = $year;
            $report->semester = $semester;
            $report->editor = $userId;
            $report->editDate = $timeStamp;
            $this->save($report);
        }
    }
    public function deleteReport($reportId){
        $this->deleteById($reportId);
    }
    public function getAllReports($groupId){
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId',$groupId);
        $reports =  $this->findListByExample($ex);
        return $reports;

    }
    public function getReports($groupId,$first,$count){
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId',$groupId);
        $ex->setLimitClause($first,$count);
        $reports =  $this->findListByExample($ex);
        $result =$this->buildResultFromObjectList($reports);
        return $result;
    }
    public function getNumberOfReports($groupId){
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId',$groupId);
        $count =$this->countByExample($ex);
        return $count;
    }
    public function getDateOfLastReport($groupId){
        $query = "SELECT MAX(`createDate`) FROM `". $this->getTableName()."` WHERE `groupId`= :groupId";
        $maxDate =  $this->dbo->queryForColumn($query, array('groupId' => $groupId));
        if($maxDate == null){
            return 0;
        }
        return $maxDate;
    }

    public function getSemesterReport($groupId,$year,$semester,$first,$count){
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId',$groupId);
        if(!empty($year) && $year!=-1){
            $ex->andFieldEqual('year',$year);
        }
        if($semester == 1 || $semester ==2){
            $ex->andFieldEqual('semester',$semester);
        }
        $ex->setLimitClause($first,$count);
        $reports =  $this->findListByExample($ex);
        return $this->buildResultFromObjectList($reports);
    }
    public function getNumberOfSemesterReport($groupId,$year,$semester){
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId',$groupId);
        if(!empty($year) && $year!=-1){
            $ex->andFieldEqual('year',$year);
        }
        if($semester == 1 || $semester ==2){
            $ex->andFieldEqual('semester',$semester);
        }
        $count =$this->countByExample($ex);
        return $count;
    }

    public function getOverallReports($first,$count){
        $result = array();
        $activationTable = FRMREPORT_BOL_ActivationDao::getInstance()->getTableName();
        $reportTable = $this->getTableName();
        $groupTable = GROUPS_BOL_GroupDao::getInstance()->getTableName();
        $query='SELECT `activations`.`groupId` AS `groupId`,COUNT(`reports`.`id`) AS `reportCount`, `title` AS `groupTitle` 
                From `'.$activationTable . '` As `activations` 
                LEFT JOIN `' . $reportTable . '` AS `reports` ON  `activations`.`groupId` = `reports`.`groupId` 
                INNER JOIN `'.$groupTable . '` AS `groups` ON `activations`.`groupId` = `groups`.`id` GROUP By groupId LIMIT :f, :c';
        $reports= OW::getDbo()->queryForList($query,array('f'=>$first , 'c'=>$count));
        foreach ($reports as $report){
            $id = $report['groupId'];
            $result[$id]['groupId'] = $id;
            $result[$id]['reportCount'] = $report['reportCount'];
            $result[$id]['groupTitle'] = $report['groupTitle'];
            $lastCreateDate =  $this->getDateOfLastReport($id);
            if($lastCreateDate>0){
                $result[$id]['lastReport'] =UTIL_DateTime::formatSimpleDate($lastCreateDate,true);
            }else{
                $result[$id]['lastReport']='';
            }
        }
        return $result;
    }
    public function getOverallReportsOfGroup($groupId,$first,$count){
        $result = array();
        $activationTable = FRMREPORT_BOL_ActivationDao::getInstance()->getTableName();
        $reportTable = $this->getTableName();
        $groupTable = GROUPS_BOL_GroupDao::getInstance()->getTableName();

        $query='SELECT `activations`.`groupId` AS `groupId`,COUNT(`reports`.`id`) AS `reportCount`, `title` AS `groupTitle` 
                From `'.$activationTable . '` As `activations` 
                LEFT JOIN `' . $reportTable . '` AS `reports` ON  `activations`.`groupId` = `reports`.`groupId` 
                INNER JOIN `'.$groupTable . '` AS `groups` ON `activations`.`groupId` = `groups`.`id` GROUP By groupId HAVING groupId=:groupId LIMIT :f, :c';

        $reports= OW::getDbo()->queryForList($query,array('groupId'=>$groupId,'f'=>$first,'c'=>$count));
        foreach ($reports as $report){
            $id = $report['groupId'];
            $result[$id]['groupId'] = $id;
            $result[$id]['reportCount'] = $report['reportCount'];
            $result[$id]['groupTitle'] = $report['groupTitle'];
            $lastCreateDate =  $this->getDateOfLastReport($id);
            if($lastCreateDate>0){
                $result[$id]['lastReport'] =UTIL_DateTime::formatSimpleDate($lastCreateDate,true);
            }else{
                $result[$id]['lastReport']='';
            }
        }
        return $result;
    }
    public function findOverallReportsOfGroup($title,$first,$count){
        if(empty($title)){
            return $this->getOverallReports($first,$count);
        }
        $result = array();
        $activationTable = FRMREPORT_BOL_ActivationDao::getInstance()->getTableName();
        $reportTable = $this->getTableName();
        $groupTable = GROUPS_BOL_GroupDao::getInstance()->getTableName();

        $query="SELECT `activations`.`groupId` AS `groupId`,COUNT(`reports`.`id`) AS `reportCount`, `title` AS `groupTitle` 
                From `".$activationTable . "` As `activations` 
                LEFT JOIN `" . $reportTable . "` AS `reports` ON  `activations`.`groupId` = `reports`.`groupId` 
                INNER JOIN `".$groupTable . "` AS `groups` ON `activations`.`groupId` = `groups`.`id` GROUP By groupId HAVING groupTitle Like :title LIMIT :f, :c";

        $reports= OW::getDbo()->queryForList($query,array('title'=>"%{$title}%",'f'=>$first,'c'=>$count));
        foreach ($reports as $report){
            $id = $report['groupId'];
            $result[$id]['groupId'] = $id;
            $result[$id]['reportCount'] = $report['reportCount'];
            $result[$id]['groupTitle'] = $report['groupTitle'];
            $lastCreateDate =  $this->getDateOfLastReport($id);
            if($lastCreateDate>0){
                $result[$id]['lastReport'] =UTIL_DateTime::formatSimpleDate($lastCreateDate,true);
            }else{
                $result[$id]['lastReport']='';
            }
        }
        return $result;
    }

    public function hasConflictWithOtherReports($groupId,$reportId,$year,$semester){
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId',$groupId);
        $ex->andFieldNotEqual('id',$reportId);
        $ex->andFieldEqual('year',$year);
        $ex->andFieldEqual('semester',$semester);
        $count = $this->countByExample($ex);
        if($count>0){
            return true;
        }else{
            return false;
        }
    }
    public function canCreateReport($groupId){
        if (!OW::getUser()->isAuthenticated()){
            return false;
        }

        if(!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return false;
        }

        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if(!isset($group)){
            return false;
        }

        $reportService = FRMREPORT_BOL_Service::getInstance();
        if(!$reportService->isReportWidgetEnable($groupId)){
            return false;
        }


        if(GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group) ||  OW::getUser()->isAuthorized('frmreport')){
            return true;
        }else{
            return false;
        }
    }
    public function canEditReport($reportId){
        if (!OW::getUser()->isAuthenticated()){
            return false;
        }

        if(!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return false;
        }

        $report = $this->findReport($reportId);
        if(!isset($report)){
            return false;
        }

        $groupId = $report->groupId;
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if(!isset($group)){
            return false;
        }

        $reportService = FRMREPORT_BOL_Service::getInstance();
        if(!$reportService->isReportWidgetEnable($groupId)){
            return false;
        }

        if(OW::getUser()->isAuthorized('frmreport') ){
            return true;
        }

        return GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group);
    }
    public function canViewReport($reportId){
        if(!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return false;
        }

        $report = $this->findReport($reportId);
        if(!isset($report)){
            return false;
        }

        $groupId = $report->groupId;
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if(!isset($group)){
            return false;
        }

        if(OW::getUser()->isAuthorized('frmreport') || OW::getUser()->isAdmin()){
            return true;
        }


        $reportService = FRMREPORT_BOL_Service::getInstance();
        if(!$reportService->isReportWidgetEnable($groupId)){
            return false;
        }

        return GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group);
    }
    public function canViewReportsOfGroup($groupId){
        if(!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return false;
        }

        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if(!isset($group)){
            return false;
        }

        if(OW::getUser()->isAuthorized('frmreport')){
            return true;
        }

        $reportService = FRMREPORT_BOL_Service::getInstance();
        if(!$reportService->isReportWidgetEnable($groupId)){
            return false;
        }

        return GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group);
    }

    public function findReport($id){
        $userService=BOL_UserService::getInstance();
        $report = $this->findById($id);
        $report->creator = $userService->getDisplayName($report->creator);
        $report->editor = $userService->getDisplayName($report->editor);
        $report->createDate = UTIL_DateTime::formatSimpleDate($report->createDate,true);
        $report->editDate = UTIL_DateTime::formatSimpleDate($report->editDate,true);
        return $report;
    }
    public function findGroupOfReport($reportId){
        $report = $this->findReport($reportId);
        if(!isset($report)){
            return -1;
        }else{
            $groupId = $report->groupId;
            return $groupId;
        }
    }

    private function buildResultFromArrayList($reports){
        $result = array();
        foreach ($reports as $report){
            $id = $report['id'];
            $result[$id]['id'] = $report['id'];
            $result[$id]['groupId'] = $report['groupId'];
            $result[$id]['creator'] = $report['creator'];
            $result[$id]['editor'] = $report['editor'];
            $result[$id]['createDate'] = $report['createDate'];
            $result[$id]['editDate'] = $report['editDate'];
            $result[$id]['semester'] = $report['semester'];
            $result[$id]['year'] = $report['year'];
        }
        return $result;
    }
    private function buildResultFromObjectList($reports){
        $result = array();
        foreach ($reports as $report){
            $userService=BOL_UserService::getInstance();
            $creator= $userService->getDisplayName($report->creator);
            $editor = $userService->getDisplayName($report->editor);
            $id = $report->id;
            $result[$id]['id'] = $id;
            $result[$id]['groupId'] = $report->groupId;
            $result[$id]['creator'] = $creator;
            $result[$id]['editor'] = $editor;
            $result[$id]['createDate'] = UTIL_DateTime::formatSimpleDate($report->createDate,true);
            $result[$id]['editDate'] = UTIL_DateTime::formatSimpleDate($report->editDate,true);
            $result[$id]['semester'] = $report->semester;
            $result[$id]['year'] = $report->year;
        }
        return $result;
    }

}