<?php
class FRMINVITE_BOL_InvitationDetails extends OW_Entity
{

    public $senderId;
    public $invitedEmail;
    public $timeStamp;

    /**
     * @return mixed
     */
    public function getSenderId()
    {
        return $this->senderId;
    }

    /**
     * @param mixed $senderId
     */
    public function setSenderId($senderId)
    {
        $this->senderId = $senderId;
    }

    /**
     * @return mixed
     */
    public function getInvitedEmail()
    {
        return $this->invitedEmail;
    }

    /**
     * @param mixed $invitedEmail
     */
    public function setInvitedEmail($invitedEmail)
    {
        $this->invitedEmail = $invitedEmail;
    }

    /**
     * @return mixed
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /**
     * @param mixed $timeStamp
     */
    public function setTimeStamp($timeStamp)
    {
        $this->timeStamp = $timeStamp;
    }



}
