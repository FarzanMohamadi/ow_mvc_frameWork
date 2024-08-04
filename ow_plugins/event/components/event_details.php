<?php

class EVENT_CMP_EventDetails extends BASE_CLASS_Widget
{

    private $eventService;

    /**
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params ) {
        parent::__construct();

        $this->eventService = EVENT_BOL_EventService::getInstance();
        $eventId = $params->additionalParamList['entityId'];
        $event = $this->eventService->findEvent($eventId);

        $infoArray = array(
            'id' => $event->getId(),
            'date' => UTIL_DateTime::formatSimpleDate($event->getStartTimeStamp(), $event->getStartTimeDisable()),
            'endDate' => $event->getEndTimeStamp() === null || !$event->getEndDateFlag() ? null : (UTIL_DateTime::formatSimpleDate($event->getEndTimeDisable() ? strtotime("-1 day", $event->getEndTimeStamp()) : $event->getEndTimeStamp(),$event->getEndTimeDisable())),
            'location' => $event->getLocation(),
            'creatorName' => BOL_UserService::getInstance()->getDisplayName($event->getUserId()),
            'creatorLink' => BOL_UserService::getInstance()->getUserUrl($event->getUserId()),
        );

        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_EVENT_SELECTED_CATEGORY_LABEL, array('eventId' => $event->id)));
        if(isset($resultsEvent->getData()['categoryLabel'])) {
            $infoArray['categoryLabel']=$resultsEvent->getData()['categoryLabel'];
        }

        $this->assign('info', $infoArray);
        $this->assign('showEventCreatorConfig', OW::getConfig()->getValue('event', 'showEventCreator'));
    }

    public static function getStandardSettingValueList() {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('event', 'view_page_details_block_cap_label'),
            self::SETTING_ICON => self::ICON_FILE
        );
    }

    public static function getAccess() {
        return self::ACCESS_ALL;
    }

}