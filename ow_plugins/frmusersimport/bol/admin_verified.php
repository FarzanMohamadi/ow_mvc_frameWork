<?php
/**
 * Class FRMUSERSIMPORT_BOL_AdminVerified
 */
class FRMUSERSIMPORT_BOL_AdminVerified extends OW_Entity
{

    public $email;
    public $mobile;
    public $verified;
    public $time;

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return FRMUSERSIMPORT_BOL_AdminVerified
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     * @return FRMUSERSIMPORT_BOL_AdminVerified
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     * @return FRMUSERSIMPORT_BOL_AdminVerified
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * @param mixed $verified
     * @return FRMUSERSIMPORT_BOL_AdminVerified
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;
        return $this;
    }



}
