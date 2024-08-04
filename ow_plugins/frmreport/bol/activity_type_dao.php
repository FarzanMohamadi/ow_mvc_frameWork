<?php
class FRMREPORT_BOL_ActivityTypeDao extends OW_BaseDao{
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
        return OW_DB_PREFIX . 'frmreport_activity_type';
    }

    public function getDtoClassName()
    {
        return 'FRMREPORT_BOL_ActivityType';
    }

    public function addActivityType($title){
        $activity = new FRMREPORT_BOL_ActivityType();
        $activity->title = $title;
        $this->save($activity);

    }
    public function deleteActivityType($id){
        $id = (int) $id;
        if($id>0){
            $this->deleteById($id);
        }
    }
    public function updateActivityType($id,$title){
        $activityType = $this->findById($id);
        if($activityType == null){
            return;
        }
        if($title == null || $title == ''){
            return;
        }
        $activityType->title = $title;
        $this->save($activityType);
    }
    public function getActivityType($id){
        return $this->findById($id);
    }
    public function findActivityType($title){
        $ex = new OW_Example();
        $ex->andFieldEqual('title',$title);
        return $this->findObjectByExample($ex);
    }

    public function getActivityTypeList($first,$count){
        if(isset($first) && isset($count)){
            $query = "SELECT * FROM `" . $this->getTableName()."` LIMIT :f, :c";
            return $this->dbo->queryForObjectList($query, $this->getDtoClassName(),array('f' => $first,'c' => $count));
        }else{
            $query = "SELECT * FROM `" . $this->getTableName()."`";
            return $this->dbo->queryForObjectList($query, $this->getDtoClassName());
        }
    }
    public function getActivityListCount(){
        $query = "SELECT COUNT(*) FROM `".$this->getTableName()."`";
        $count =$this->dbo->queryForColumn($query);
        return $count;
    }
    public function hasActivityTypePermission(){
        if (!OW::getUser()->isAuthenticated()){
            return false;
        }
        if(OW::getUser()->isAdmin()){
            return true;
        }else{
            return false;
        }
    }

}