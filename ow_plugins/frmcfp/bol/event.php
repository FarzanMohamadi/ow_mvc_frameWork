<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcfp.bol
 * @since 1.0
 */
class FRMCFP_BOL_Event extends OW_Entity
{
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $description;
    /**
     * @var integer
     */
    public $createTimeStamp;
    /**
     * @var integer
     */
    public $startTimeStamp;
    /**
     * @var integer
     */
    public $endTimeStamp;
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var integer
     */
    public $whoCanView;
    /**
     * @var integer
     */
    public $status = 1;
    /**
     * @var string
     */
    public $file;
    /**
     * @var string
     */
    public $image = null;
    /**
     * @var boolean
     */
    public $startTimeDisabled = false;
    /**
     * @var boolean
     */
    public $endTimeDisabled = false;
    /**
     * @var boolean
     */
    public $fileDisabled = false;
    /**
     * @var string
     */
    public $fileNote = false;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle( $title )
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription( $description )
    {
        $this->description = $description;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile( $file )
    {
        $this->file = $file;
    }

    public function getCreateTimeStamp()
    {
        return $this->createTimeStamp;
    }

    public function setCreateTimeStamp( $createTimeStamp )
    {
        $this->createTimeStamp = $createTimeStamp;
    }

    public function getStartTimeStamp()
    {
        return $this->startTimeStamp;
    }

    public function setStartTimeStamp( $startTimeStamp )
    {
        $this->startTimeStamp = $startTimeStamp;
    }

    public function getEndTimeStamp()
    {
        return $this->endTimeStamp;
    }

    public function setEndTimeStamp( $endTimeStamp )
    {
        $this->endTimeStamp = $endTimeStamp;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId( $userId )
    {
        $this->userId = $userId;
    }

    public function getWhoCanView()
    {
        return $this->whoCanView;
    }

    public function setWhoCanView( $whoCanView )
    {
        $this->whoCanView = $whoCanView;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus( $status )
    {
        $this->status = $status;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage( $image )
    {
        $this->image = $image;
    }

    public function getStartTimeDisable()
    {
        return $this->startTimeDisabled;
    }

    public function setStartTimeDisable( $flag )
    {
        $this->startTimeDisabled = (boolean)$flag;
    }
    
    public function getEndTimeDisable()
    {
        return $this->endTimeDisabled;
    }

    public function setEndTimeDisable( $flag )
    {
        $this->endTimeDisabled = (boolean)$flag;
    }

    public function getFileDisabled()
    {
        return $this->fileDisabled;
    }

    public function setFileDisabled($fileDisabled)
    {
        $this->fileDisabled = (bool)$fileDisabled;
    }

    public function getFileNote()
    {
        return $this->fileNote;
    }

    public function setFileNote($fileNote)
    {
        $this->fileNote = (string)$fileNote;
    }
}

