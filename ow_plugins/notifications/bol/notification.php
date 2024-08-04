<?php
/**
 * Data Transfer Object for `notifications_notification` table.
 *
 * @package notifications.bol
 * @since 1.0
 */
class NOTIFICATIONS_BOL_Notification extends OW_Entity
{
    /**
     * @var string
     */
    public $entityType;

    /**
     * @var string
     */
    public $entityId;

    /**
     * @var int
     */
    public $userId;

    /**
     *
     * @var string
     */
    public $pluginKey;

    /**
     * @var int
     */
    public $timeStamp;

    /**
     *
     * @var int
     */
    public $viewed = false;

    /**
     *
     * @var int
     */
    public $sent = false;

    /**
     *
     * @var int
     */
    public $active = true;

    /**
     *
     * @var string
     */
    public $action;

    /**
     * @var string
     */
    public $data;

    public function setData( $data )
    {
        $string = $this->convertDataToString($data);
        $this->data = $string;
    }

    public function getData()
    {
        return $this->convertStringToData($this->data);
    }

    /***
     * @param $data
     * @return false|string
     */
    public function convertDataToString($data){
        $homeUrlCorrectorEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NOTIFICATION_STRING_WRITE, array('data' => $data)));
        if(isset($homeUrlCorrectorEvent->getData()['data'])){
            $data = $homeUrlCorrectorEvent->getData()['data'];
        }
        if (isset($data['cache'])) {
            unset($data['cache']);
        }
        return json_encode($data);
    }

    /***
     * @param $string
     * @return mixed|null
     */
    public function convertStringToData($string){
        $homeUrlCorrectorEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NOTIFICATION_STRING_READ, array('data' => $string)));
        if(isset($homeUrlCorrectorEvent->getData()['data'])){
            $string = $homeUrlCorrectorEvent->getData()['data'];
        }

        return empty($string) ? null : json_decode($string, true);
    }
}
