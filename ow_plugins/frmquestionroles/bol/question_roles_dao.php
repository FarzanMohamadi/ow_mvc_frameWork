<?php
/**
 * frmquestionroles
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmquestionroles
 * @since 1.0
 */

class FRMQUESTIONROLES_BOL_QuestionRolesDao extends OW_BaseDao
{
    private static $classInstance;

    private $usersByRolesData;
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'FRMQUESTIONROLES_BOL_QuestionRoles';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmquestionroles_question_roles';
    }

    public function getUnapprovedUsersByRolesData($rolesData, $first = 0, $rowCount = 20) {
        if (empty($rolesData)) {
            return array();
        }

        $queryCondition = '';
        $dataSize = 0;
        $count = 0;

        foreach ($rolesData as $rolesDatum) {
            $data = (array) json_decode($rolesDatum->data);
            if ($data && is_array($data) && !empty($data)) {
                $dataSize += sizeof($data);
            }
        }
        foreach ($rolesData as $rolesDatum) {
            $data = (array) json_decode($rolesDatum->data);
            if ($data && is_array($data) && !empty($data)) {
                foreach ($data as $key => $value) {
                    $count++;
                    $queryCondition .= " ( `qd`.`questionName` = '" . $this->dbo->escapeValue($key)  . "' AND `qd`.`intValue` = '" . $this->dbo->escapeValue($value)  . "') ";
                    if ($count < $dataSize) {
                        $queryCondition .= ' or ';
                    }
                }
            }
        }

        $query = "SELECT `u`.id
            FROM `" . BOL_UserDao::getInstance()->getTableName() . "` as `u`
            INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd`
            	ON (`qd`.userId = `u`.`id`)
            LEFT JOIN `" . BOL_UserApproveDao::getInstance()->getTableName() . "` as `d`
                ON( `u`.`id` = `d`.`userId` )
            WHERE `d`.`id` IS NOT NULL and (" . $queryCondition . ")
            ORDER BY `u`.`activityStamp` DESC
        ";

        $userIds = $this->dbo->queryForColumnList($query);
        return BOL_UserService::getInstance()->getUsersViewQuestions($userIds);
    }

    public function getUsersByRolesData($rolesData, $first = 0, $rowCount = 20) {
        if(isset($this->usersByRolesData))
        {
            return $this->usersByRolesData;
        }
        if (empty($rolesData)) {
            return array();
        }

        $queryCondition = '';
        $dataSize = 0;
        $count = 0;

        foreach ($rolesData as $rolesDatum) {
            $data = (array) json_decode($rolesDatum->data);
            if ($data && is_array($data) && !empty($data)) {
                $dataSize += sizeof($data);
            }
        }
        foreach ($rolesData as $rolesDatum) {
            $data = (array) json_decode($rolesDatum->data);
            if ($data && is_array($data) && !empty($data)) {
                foreach ($data as $key => $value) {
                    if($value =='equal')
                    {
                        $questionDataValue = BOL_QuestionDataDao::getInstance()->findByQuestionAndUser($key,OW::getUser()->getId());
                        $value = $questionDataValue->intValue;
                    }
                    $count++;
                    $queryCondition .= " ( `qd`.`questionName` = '" . $key . "' AND `qd`.`intValue` = " . $value . ") ";
                    if ($count < $dataSize) {
                        $queryCondition .= ' or ';
                    }
                }
            }
        }

        $query = "SELECT `u`.id
            FROM `" . BOL_UserDao::getInstance()->getTableName() . "` as `u`
            INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` AS `qd`
            	ON (`qd`.userId = `u`.`id`)
            WHERE (" . $queryCondition . ")
            ORDER BY `u`.`activityStamp` DESC
        ";

        return $this->usersByRolesData = $this->dbo->queryForColumnList($query);
    }

    /***
     * @param $roleId
     * @param $data
     */
    public function saveRoleWithData($roleId, $data) {
        $role = new FRMQUESTIONROLES_BOL_QuestionRoles();
        $role->roleId = $roleId;
        $role->data = $data;
        $this->save($role);
    }

    /***
     * @param array $roleIds
     * @return array
     */
    public function findByRoleIds($roleIds = array()) {
        if (!is_array($roleIds)) {
            return array();
        }
        $example = new OW_Example();
        $example->andFieldInArray('roleId', $roleIds);
        return $this->findListByExample($example);
    }
}
