<?php
class FRMSECURITYESSENTIALS_BOL_RequestManager extends OW_Entity
{
    /**
     * @var integer
     */
    public $senderId;
    /**
     * @var integer
     */
    public $receiverId;
    /**
     * @var string
     */
    public $code;
    /**
     * @var string
     */
    public $activityType;
    /**
     * @var integer
     */
    public $expirationTimeStamp;

    /**
     * @return int
     */
    public function getSenderId()
    {
        return $this->senderId;
    }

    /**
     * @param int $senderId
     */
    public function setSenderId($senderId)
    {
        $this->senderId = $senderId;
    }

    /**
     * @return int
     */
    public function getReceiverId()
    {
        return $this->receiverId;
    }

    /**
     * @param int $receiverId
     */
    public function setReceiverId($receiverId)
    {
        $this->receiverId = $receiverId;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getExpirationTimeStamp()
    {
        return $this->expirationTimeStamp;
    }

    /**
     * @param int $expirationTimeStamp
     */
    public function setExpirationTimeStamp($expirationTimeStamp)
    {
        $this->expirationTimeStamp = (int) $expirationTimeStamp;
    }

    /**
     * @return string
     */
    public function getActivityType()
    {
        return $this->activityType;
    }

    /**
     * @param string $activityType
     */
    public function setActivityType($activityType)
    {
        $this->activityType = $activityType;
    }

}
