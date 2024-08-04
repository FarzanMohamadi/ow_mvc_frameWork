<?php
class FRMGRANT_BOL_Grant extends OW_Entity
{
    /**
     * @var integer
     */
    public $timeStamp;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $professor;

    /**
     * @var string
     */
    public $collegeAndField;

    /**
     * @var string
     */
    public $laboratory;

    /**
     * @var string
     */
    public $startedYear;

    /**
     * @var string
     */
    public $description;

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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getProfessor()
    {
        return $this->professor;
    }

    /**
     * @param string $professor
     */
    public function setProfessor($professor)
    {
        $this->professor = $professor;
    }

    /**
     * @return string
     */
    public function getCollegeAndField()
    {
        return $this->collegeAndField;
    }

    /**
     * @param string $collegeAndField
     */
    public function setCollegeAndField($collegeAndField)
    {
        $this->collegeAndField = $collegeAndField;
    }

    /**
     * @return string
     */
    public function getLaboratory()
    {
        return $this->laboratory;
    }

    /**
     * @param string $laboratory
     */
    public function setLaboratory($laboratory)
    {
        $this->laboratory = $laboratory;
    }

    /**
     * @return string
     */
    public function getStartedYear()
    {
        return $this->startedYear;
    }

    /**
     * @param string $startedYear
     */
    public function setStartedYear($startedYear)
    {
        $this->startedYear = $startedYear;
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
