<?php
class FRMREPORT_BOL_ReportDetailDao extends OW_BaseDao{
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
        return OW_DB_PREFIX . 'frmreport_report_detail';
    }

    public function getDtoClassName()
    {
        return 'FRMREPORT_BOL_ReportDetail';
    }
    public function addDetail($reportId,$activityTypeId,$count,$description){
        $reportDetail = new FRMREPORT_BOL_ReportDetail();
        $reportDetail->reportId = $reportId;
        $reportDetail->activityTypeId = $activityTypeId;
        $reportDetail->count = $count;
        $reportDetail->description = $description;
        $this->save($reportDetail);
    }
    public function getDetailsByReportId($reportId){
        $ex = new OW_Example();
        $ex->andFieldEqual('reportId',$reportId);
        $reports =  $this->findListByExample($ex);
        return $this->buildResultFromObjectList($reports);
    }
    public function getReportItem($reportId,$activityTypeId){
        $ex = new OW_Example();
        $ex->andFieldEqual('reportId',$reportId);
        $ex->andFieldEqual('activityTypeId',$activityTypeId);
        $reportItem =  $this->findObjectByExample($ex);
        return $reportItem;
    }
    public function hasReportItem($reportId,$activityTypeId){
        $ex = new OW_Example();
        $ex->andFieldEqual('reportId',$reportId);
        $ex->andFieldEqual('activityTypeId',$activityTypeId);
        $n = $this->countByExample($ex);
        if($n>0){
            return true;
        }
        else{
            return false;
        }
    }
    public function isReportExistWithThisActivity($activityTypeId){
        $ex = new OW_Example();
        $ex->andFieldEqual('activityTypeId',$activityTypeId);
        $n = $this->countByExample($ex);
        if($n>0){
            return true;
        }
        else{
            return false;
        }

    }

    public function updateReportDetail($reportDetailId,$count,$description){
        $reportDetail = $this->findById($reportDetailId);
        if(isset($reportDetail)){
            $reportDetail->count=$count;
            $reportDetail->description =$description;
            $this->save($reportDetail);
        }
    }
    public function deleteReportDetail($reportDetailId){
        $this->deleteById($reportDetailId);
    }
    public function deleteReportDetails($reportId){
        $ex = new OW_Example();
        $ex->andFieldEqual('reportId' , $reportId);
        $this->deleteByExample($ex);
    }

    private function buildResultFromArrayList($reports){
        $result = array();
        foreach ($reports as $report){
            $id = $report['id'];
            $result[$id]['id'] = $report['id'];
            $result[$id]['reportId'] = $report['reportId'];
            $result[$id]['activityTypeId'] = $report['activityTypeId'];
            $result[$id]['count'] = $report['count'];
            $result[$id]['description'] = $report['description'];
        }
        return $result;
    }
    private function buildResultFromObjectList($reports){
        $result = array();
        foreach ($reports as $report){
            $id = $report->id;
            $result[$id]['id'] = $id;
            $result[$id]['reportId'] = $report->reportId;
            $result[$id]['activityTypeId'] = $report->activityTypeId;
            $result[$id]['count'] = $report->count;
            $result[$id]['description'] = $report->description;
        }
        return $result;
    }

}