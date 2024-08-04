<?php
/**
 * Data Transfer Object for `frmcontactus_UserInformation` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontactus.bol
 * @since 1.0
 */
class FRMCONTACTUS_BOL_UserInformation extends OW_Entity
{
    /**
     * @var string
     */
    public $subject;

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @var string
     */
    public $useremail;

    /**
     * @return string
     */
    public function getUseremail()
    {
        return $this->useremail;
    }

    /**
     * @param string $useremail
     */
    public function setUseremail($useremail)
    {
        $this->useremail = $useremail;
    }

    /**
     * @var string
    */
    public $label;

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @var string
     */
    public $message;

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @var int
     */
    public $timeStamp;

    /**
     * @return int
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /**
     * @param int $timeStamp
     */
    public function setTimeStamp($timeStamp)
    {
        $this->timeStamp = $timeStamp;
    }

}
