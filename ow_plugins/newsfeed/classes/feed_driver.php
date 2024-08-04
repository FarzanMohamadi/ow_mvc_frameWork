<?php
/**
 *
 * @package ow_plugins.newsfeed.classes
 * @since 1.0
 */
class NEWSFEED_CLASS_FeedDriver extends NEWSFEED_CLASS_Driver
{
    protected function findActionList( $params )
    {
        $endTime = isset($params['endTime'])?$params['endTime']:0;
        $event = new OW_Event('newsfeed.find_action_list_by_feed',array('params'=>$params,'driver'=>$this,'end_time'=>$endTime));
        OW::getEventManager()->trigger($event);
        $actionList = $event->getData();
        $additionalInfo = array();
        if (isset($params['additionalParamList'])) {
            $additionalInfo = $params['additionalParamList'];
        }
        if(!isset($actionList)) {
            return NEWSFEED_BOL_ActionDao::getInstance()->findByFeed($params['feedType'], $params['feedId'], array($params['offset'], $params['displayCount'], $params['checkMore']), $params['startTime'], $params['formats'], $this, $endTime, $additionalInfo);
        }
        return $actionList;
    }

    protected function findActionCount( $params )
    {
        $endTime = isset($params['endTime'])?$params['endTime']:0;
        return NEWSFEED_BOL_ActionDao::getInstance()->findCountByFeed($params['feedType'], $params['feedId'], $params['startTime'], $params['formats'], $endTime);
    }

    protected function findActivityList( $params, $actionIds )
    {
        return NEWSFEED_BOL_ActivityDao::getInstance()->findFeedActivity($params['feedType'], $params['feedId'], $actionIds);
    }
}