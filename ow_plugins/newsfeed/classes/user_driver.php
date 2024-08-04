<?php
/**
 *
 * @package ow_plugins.newsfeed.classes
 * @since 1.0
 */
class NEWSFEED_CLASS_UserDriver extends NEWSFEED_CLASS_Driver
{
    protected function findActionList( $params )
    {
        $endTime = isset($params['endTime'])?$params['endTime']:0;
        $event = new OW_Event('newsfeed.find_action_list_by_user',array('params'=>$params,'driver'=>$this,'end_time'=>$endTime));
        OW::getEventManager()->trigger($event);
        $actionList = $event->getData();
        if(!isset($actionList)) {
            return NEWSFEED_BOL_ActionDao::getInstance()->findByUser($params['feedId'], array($params['offset'], $params['displayCount'], $params['checkMore']), $params['startTime'], $params['formats'], $this, $endTime);
        }
        return $actionList;
    }

    protected function findActionCount( $params )
    {
        $endTime = isset($params['endTime'])?$params['endTime']:0;
        return NEWSFEED_BOL_ActionDao::getInstance()->findCountByUser($params['feedId'], $params['startTime'], $params['formats'], $endTime);
    }

    protected function findActivityList( $params, $actionIds )
    {
        return NEWSFEED_BOL_ActivityDao::getInstance()->findUserFeedActivity($params['feedId'], $actionIds );
    }
}