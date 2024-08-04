<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmshasta.bol
 * @since 1.0
 */
class FRMSHASTA_BOL_FileDao extends OW_BaseDao
{
    private static $classInstance;

    /***
     * @return FRMSHASTA_BOL_FileDao
     */
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
        return 'FRMSHASTA_BOL_File';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmshasta_file';
    }

    /***
     * @param $name
     * @param $userId
     * @param $uploadTime
     * @param $time
     * @param $categoryId
     * @param $fileName
     * @param $keywords
     * @param null $id
     * @return FRMSHASTA_BOL_File|null
     */
    public function saveFile($name, $userId, $uploadTime, $time, $categoryId, $fileName, $keywords, $id = null) {
        $file = null;
        if ($id != null) {
            $file = $this->findById($id);
        } else {
            $file = new FRMSHASTA_BOL_File();
        }
        $file->name = $name;
        $file->uploadTime = $uploadTime;
        $file->time = $time;
        $file->userId = $userId;
        $file->categoryId = $categoryId;
        $file->fileName = $fileName;
        $file->keywords = $keywords;
        $this->save($file);
        return $file;
    }

    /***
     * @param $userId
     * @param $first
     * @param $count
     * @return array
     */
    public function getMyFiles($userId, $first, $count) {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->setLimitClause($first, $count);
        $example->setOrder('time desc');
        return $this->findListByExample($example);
    }

    public function getQuery($validCompanyIds, $searchCompanyIds, $categoryId, $fromDate, $toDate, $first = 0, $count = 10, $fileId = null, $countQuery = false) {
        $query = 'select f.* ';
        if ($countQuery) {
            $query = 'select count(*) count ';
        }

        $pref = OW_DB_PREFIX;

        $query .= " from {$pref}frmshasta_file f, {$pref}frmshasta_user_company uc, {$pref}frmshasta_company c 
                where f.userId = uc.userId and uc.companyId = c.id ";

        if ($searchCompanyIds != null && is_array($searchCompanyIds) && sizeof($searchCompanyIds) > 0) {
            $query .= ' and uc.companyId in (' . $this->dbo->mergeInClause($searchCompanyIds) . ') ';
        }

        if ($validCompanyIds != null && is_array($validCompanyIds) && sizeof($validCompanyIds) > 0) {
            $query .= ' and uc.companyId in (' . $this->dbo->mergeInClause($validCompanyIds) . ') ';
        }

          $query .= ' and f.categoryId not in (select categoryId from ' . FRMSHASTA_BOL_UserCategoryAccessDao::getInstance()->getTableName() . ' where userId = ' . OW::getUser()->getId() .' AND  access="'.FRMSHASTA_BOL_UserCategoryAccessDao::ACCESS_DENIED.'")';

          $query .= ' and f.id not in (select fileId from ' . FRMSHASTA_BOL_UserFileAccessDao::getInstance()->getTableName() .' where userId = ' . OW::getUser()->getId() .' AND  access="'.FRMSHASTA_BOL_UserFileAccessDao::ACCESS_DENIED.'")';

        if ($categoryId != null && is_numeric($categoryId)) {
            $query .= ' and f.categoryId = ' . $categoryId;
        }

        if ($fromDate != null && is_numeric($fromDate)) {
            $query .= ' and f.time >= ' . $fromDate;
        }
        if ($toDate != null && is_numeric($toDate)) {
            $query .= ' and f.time <= ' . $toDate;
        }

        if ($fileId != null && is_numeric($fileId)) {
            $query .= ' and f.id = ' . $fileId;
        }

        if (!$countQuery) {
            $query .= ' order by f.time desc limit ' . $first . ',' . $count;
        }
        return $query;
    }

    /***
     * @param $validCompanyIds
     * @param $searchCompanyIds
     * @param null $categoryId
     * @param null $fromDate
     * @param null $toDate
     * @param int $first
     * @param int $count
     * @param int $fileId
     * @return array
     */
    public function getFiles($validCompanyIds, $searchCompanyIds, $categoryId = null, $fromDate = null, $toDate = null, $first = 0, $count = 10, $fileId = null) {
        $query = $this->getQuery($validCompanyIds, $searchCompanyIds, $categoryId, $fromDate, $toDate, $first, $count, $fileId);
        $list =  $this->dbo->queryForList($query);

        $fileIds = array();
        foreach ($list as $item) {
            $fileIds[] = $item['id'];
        }
        return $this->getFilesByIds($fileIds);
    }

    public function getFilesByIds($fileIds) {
        if (sizeof($fileIds) == 0) {
            return array();
        }
        $example = new OW_Example();
        $example->andFieldInArray('id', $fileIds);
        $example->setOrder('time desc');

        return $this->findListByExample($example);
    }

    /***
     * @param $validCompanyIds
     * @param $searchCompanyIds
     * @param null $categoryId
     * @param null $fromDate
     * @param null $toDate
     * @param null $fileId
     * @return int
     */
    public function getFilesCount($validCompanyIds, $searchCompanyIds, $categoryId = null, $fromDate = null, $toDate = null, $fileId = null) {
        $query = $this->getQuery($validCompanyIds, $searchCompanyIds, $categoryId, $fromDate, $toDate, 0, 10, $fileId, true);
        $list =  $this->dbo->queryForRow($query);

        if (isset($list['count'])) {
            return (int) $list['count'];
        }
        return 0;
    }

    public function getUserFiles($userId, $categoryId = null, $fromDateValue = null, $toDateValue = null, $first = 0, $count = 10) {
        $example = new OW_Example();
        if ($userId != null) {
            $example->andFieldEqual('userId', $userId);
        }
        if ($categoryId != null) {
            $example->andFieldEqual('categoryId', $categoryId);
        }
        if ($fromDateValue != null) {
            $example->andFieldGreaterThenOrEqual('time', $fromDateValue);
        }
        if ($toDateValue != null) {
            $example->andFieldLessOrEqual('time', $toDateValue);
        }
        $example->setOrder('`time` DESC');
        $example->setLimitClause($first, $count);
        return $this->findListByExample($example);
    }

    public function getUserFilesCount($userId, $categoryId = null, $fromDateValue = null, $toDateValue = null) {
        $example = new OW_Example();
        if ($userId != null) {
            $example->andFieldEqual('userId', $userId);
        }
        if ($categoryId != null) {
            $example->andFieldEqual('categoryId', $categoryId);
        }
        if ($fromDateValue != null) {
            $example->andFieldGreaterThenOrEqual('time', $fromDateValue);
        }
        if ($toDateValue != null) {
            $example->andFieldLessOrEqual('time', $toDateValue);
        }
        $example->setOrder('`time` DESC');
        return $this->countByExample($example);
    }

    public function getCategoryFiles($categoryId, $first = 0, $count = 10) {
        $example = new OW_Example();
        $example->andFieldEqual('categoryId', $categoryId);
        $example->setOrder('`time` DESC');
        $example->setLimitClause($first, $count);
        return $this->findListByExample($example);
    }
}
