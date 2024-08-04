<?php
/**
 *
 * @package ow_plugins.newsfeed.controllers
 * @since 1.0
 */
class NEWSFEED_MCTRL_Ajax extends NEWSFEED_CTRL_Ajax
{
    protected function afterLike($entityType, $entityId) 
    {
        $mcmp = new NEWSFEED_MCMP_Likes($entityType, $entityId);

        echo json_encode(array(
            'count' => $mcmp->getCount(),
            'markup' => $mcmp->render()
        ));

        exit;
    }
    
    protected function afterUnlike($entityType, $entityId) 
    {
        $this->afterLike($entityType, $entityId);
    }
    
    protected function createFeedItem($action, $sharedData) 
    {
        return OW::getClassInstance("NEWSFEED_MCMP_FeedItem", $action, $sharedData);
    }
    
    protected function createFeedList($actionList, $data) 
    {
        return OW::getClassInstance("NEWSFEED_MCMP_FeedList", $actionList, $data);
    }


}