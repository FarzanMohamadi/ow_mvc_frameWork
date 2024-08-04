<?php
class FRMGROUPSPLUS_BOL_GroupSetting extends OW_Entity
{

    public $groupId;
    public $whoCanUploadFile;
    public $whoCanCreateTopic;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return FRMGROUPSPLUS_BOL_GroupSetting
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param mixed $groupId
     * @return FRMGROUPSPLUS_BOL_GroupSetting
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWhoCanUploadFile()
    {
        return $this->whoCanUploadFile;
    }

    /**
     * @param mixed $whoCanUploadFile
     * @return FRMGROUPSPLUS_BOL_GroupSetting
     */
    public function setWhoCanUploadFile($whoCanUploadFile)
    {
        $this->whoCanUploadFile = $whoCanUploadFile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWhoCanCreateTopic()
    {
        return $this->whoCanCreateTopic;
    }

    /**
     * @param mixed $whoCanCreateTopic
     * @return FRMGROUPSPLUS_BOL_GroupSetting
     */
    public function setWhoCanCreateTopic($whoCanCreateTopic)
    {
        $this->whoCanCreateTopic = $whoCanCreateTopic;
        return $this;
    }


}
