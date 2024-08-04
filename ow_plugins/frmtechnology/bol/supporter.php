<?php
class FRMTECHNOLOGY_BOL_Supporter extends OW_Entity
{

    /**
     * @var integer
     */
    public $technologyId;

    /**
     * @var integer
     */
    public $userId;

    /**
     * @return mixed
     */
    public function getTechnologyId()
    {
        return $this->technologyId;
    }

    /**
     * @param mixed $technologyId
     */
    public function setTechnologyId($technologyId)
    {
        $this->technologyId = $technologyId;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }


}