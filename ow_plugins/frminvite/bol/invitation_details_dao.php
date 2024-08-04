<?php
class FRMINVITE_BOL_InvitationDetailsDao extends OW_BaseDao
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
        return 'FRMINVITE_BOL_InvitationDetails';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frminvite_details';
    }

    /***
     * @param $senderId
     * @param $invitedEmail
     * @return bool|void
     */
    public function addInvitationDetails($senderId,$invitedEmail )
    {
        if (!isset($senderId) || !isset($invitedEmail)) {
            return;
        }
        $invitationDetails = new FRMINVITE_BOL_InvitationDetails();
        $invitationDetails->setSenderId($senderId);
        $invitationDetails->setInvitedEmail($invitedEmail);
        $invitationDetails->setTimeStamp(time());
        $this->save($invitationDetails);
        return true;
    }

    public function getInvitationDetailsData($first,$count)
    {
        $data = array();
        $queryGetAllData = 'select `senderId`,`invitedEmail`,`timeStamp` from '.  self::getTableName() .' ORDER BY `timeStamp` DESC LIMIT :first, :count';
        $data['data'] = OW::getDbo()->queryForList($queryGetAllData, array('first' => (int) $first, 'count' => (int) $count));
        return $data;
    }
    public function getInvitationDetailsDataCount()
    {
        $query = "SELECT COUNT(*) FROM `" . $this->getTableName()."`";
        return $this->dbo->queryForColumn($query);
    }

}
