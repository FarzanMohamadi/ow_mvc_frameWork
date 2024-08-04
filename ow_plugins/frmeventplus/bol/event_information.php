<?php
/**
 * Data Transfer Object for `frmeventplus_EventInformation` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmeventplus.bol
 * @since 1.0
 */
class FRMEVENTPLUS_BOL_EventInformation extends OW_Entity
{
    /**
     * @var integer
     */
    public $eventId;

    /**
     * @return integer
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @param string integer
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;
    }

   /**
     * @var integer
    */
    public $categoryId;

    /**
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param integer $categoryId
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

}
