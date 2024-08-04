<?php
class FRMTECHNOLOGY_BOL_Order extends OW_Entity
{

    /**
     * @var integer
     */
    public $technologyId;

    /**
     * @var integer
     */
    public $timeStamp;
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $companyName;

    /**
     * @var string
     */
    public $companyWebsite;

    /**
     * @var string
     */
    public $jobTitle;

    /**
     * @var string
     */
    public $companyAddress;

    /**
     * @var string
     */
    public $companyActivityField;

    /**
     * @var string
     */
    public $description;

    /**
     * @return integer
     */
    public function getTechnologyId()
    {
        return $this->technologyId;
    }

    /**
     * @param integer $technologyId
     */
    public function setTechnologyId($technologyId)
    {
        $this->technologyId = $technologyId;
    }

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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string
     */
    public function getCompanyWebsite()
    {
        return $this->companyWebsite;
    }

    /**
     * @param string $companyWebsite
     */
    public function setCompanyWebsite($companyWebsite)
    {
        $this->companyWebsite = $companyWebsite;
    }

    /**
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @param string $jobTitle
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;
    }

    /**
     * @return string
     */
    public function getCompanyAddress()
    {
        return $this->companyAddress;
    }

    /**
     * @param string $companyAddress
     */
    public function setCompanyAddress($companyAddress)
    {
        $this->companyAddress = $companyAddress;
    }

    /**
     * @return string
     */
    public function getCompanyActivityField()
    {
        return $this->companyActivityField;
    }

    /**
     * @param string $companyActivityField
     */
    public function setCompanyActivityField($companyActivityField)
    {
        $this->companyActivityField = $companyActivityField;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }





}


