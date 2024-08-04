<?php
class FRMREPORT_BOL_ActivationDao extends OW_BaseDao{
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
        return OW_DB_PREFIX . 'frmreport_activation';
    }

    public function getDtoClassName()
    {
        return 'FRMREPORT_BOL_Activation';
    }
    public function enableReportWidget($groupId){
        if(!$this->isReportWidgetEnable($groupId)){
            $activation = new FRMREPORT_BOL_Activation();
            $activation->groupId = $groupId;
            $this->save($activation);
        }
    }
    public function disableReportWidget($groupId){
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId',$groupId);
        $this->deleteByExample($ex);
    }
    public function isReportWidgetEnable($groupId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId',$groupId);
        $count =$this->countByExample($ex);
        if($count>0){
            return true;
        }else{
            return false;
        }
    }
    public function getGroupsWithActiveReportWidget(){
        $result = array();
        $groupTable = GROUPS_BOL_GroupDao::getInstance()->getTableName();
        $activationTable = $this->getTableName();
        $query ='SELECT `groups`.`title`,`groups`.`id` 
                  FROM `'.$groupTable.'` AS `groups` 
                  INNER JOIN `'.$activationTable.'` AS `activations` ON `groups`.`id`=`activations`.`groupId`';
        $groups= OW::getDbo()->queryForList($query);
        foreach ($groups as $group){
            $id = $group['id'];
            $result[$id]['id'] = $id;
            $result[$id]['title'] = $group['title'];
        }
        return $result;
    }
    public function getNumberOfGroupsWithActiveReportWidget(){
        $query = "SELECT COUNT(*) FROM `".$this->getTableName()."`";
        $count =$this->dbo->queryForColumn($query);
        return $count;
    }
    public function findNumberOfGroupsWithActiveReportWidget($title){
        $groupTable = GROUPS_BOL_GroupDao::getInstance()->getTableName();
        $activationTable = $this->getTableName();
        $query ='SELECT COUNT(*) 
                  FROM `'.$groupTable.'` AS `groups` 
                  INNER JOIN `'.$activationTable.'` AS `activations` ON `groups`.`id`=`activations`.`groupId` WHERE title LIKE :title';
        $count = $this->dbo->queryForColumn($query,array('title'=>"%{$title}%"));
        return $count;
    }
}