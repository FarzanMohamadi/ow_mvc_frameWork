<?php
class FRMMASSMAILING_BOL_MailingDetailsDao extends OW_BaseDao
{

    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'FRMMASSMAILING_BOL_MailingDetails';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmmassmailing_details';
    }

    /***
     * @param $title
     * @param $body
     * @param $roles
     * @return bool|void
     */
    public function addMassMailingDetails($title,$body ,$roles )
    {
        if (!isset($title) || !isset($roles) || !isset($body) || !OW::getUser()->isAdmin()) {
            return;
        }
        $mailingDetails = new FRMMASSMAILING_BOL_MailingDetails();
        $mailingDetails->setTitle($title);
        $mailingDetails->setRoles($roles);
        $mailingDetails->setBody($body);
        $mailingDetails->setCreateTimeStamp(time());
        $this->save($mailingDetails);
        return true;
    }

    public function getMassMailingDetailsData($first,$count)
    {
        $data = array();
        $queryGetAllData = 'select `roles`,`title`,`body`,`createTimeStamp` from '.  self::getTableName() .' ORDER BY `createTimeStamp` DESC LIMIT :first, :count';
        $data['data'] = OW::getDbo()->queryForList($queryGetAllData, array('first' => (int) $first, 'count' => (int) $count));
        return $data;
    }
    public function getMassMailingDetailsDataCount()
    {
        $query = "SELECT COUNT(*) FROM `" . $this->getTableName()."`";
        return $this->dbo->queryForColumn($query);
    }

}
