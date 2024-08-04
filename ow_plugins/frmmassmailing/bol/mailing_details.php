<?php
class FRMMASSMAILING_BOL_MailingDetails extends OW_Entity
{

    public $roles;
    public $title;
    public $body;
    public $createTimeStamp;

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getCreateTimeStamp()
    {
        return $this->createTimeStamp;
    }

    /**
     * @param mixed $createTimeStamp
     */
    public function setCreateTimeStamp($createTimeStamp)
    {
        $this->createTimeStamp = $createTimeStamp;
    }

}
