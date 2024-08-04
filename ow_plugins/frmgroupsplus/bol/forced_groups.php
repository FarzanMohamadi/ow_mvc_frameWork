<?php
class FRMGROUPSPLUS_BOL_ForcedGroups extends OW_Entity
{

    public $groupId;
    public $canLeave;
    public $condition;




    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param mixed $groupId
     * @return FRMGROUPSPLUS_BOL_ForcedGroups
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCanLeave()
    {
        return $this->canLeave;
    }

    /**
     * @return mixed
     */
    public function getCondition()
    {
        return $this->condition;
    }


}
