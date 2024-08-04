<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmhashtag
 * @since 1.0
 */
class FRMHASHTAG_MCMP_Event extends OW_MobileComponent
{

    public function __construct( array $idList, $allCount, $page = 1)
    {
        parent::__construct();

        $eventService = EVENT_BOL_EventService::getInstance();
        $ids = array();
        foreach ($idList as $element)
            $ids[] = $element['id'];
        $events = $eventService->findEventsWithIds($ids, 1, 500, true);

        $existingEvent = $eventService ->findByIdList($ids);
        //delete removed ids
        $existingEntityIds = array();
        foreach($existingEvent as $item){
            $existingEntityIds[] = $item->id;
        }
        if(count($idList)>count($existingEntityIds)){
            $deletedEntityIds = array();
            foreach($idList as $key=>$element){
                if(!in_array($element['id'], $existingEntityIds)){
                    if($eventService->findEvent($element['id']) == null ) {
                        $deletedEntityIds[] = $key;
                    }
                }
            }
            FRMHASHTAG_BOL_Service::getInstance()->deleteEntitiesByListIds($deletedEntityIds);
        }
        $allCount = count($existingEntityIds);

        //paging
        $rpp = 10;
        foreach($events as $item){
            $visibleEntityIds[] = $item->id;
        }
        $itemsCount = count($visibleEntityIds);
        if($page>0 && $page<=ceil($itemsCount / $rpp)) {
            $paging = new BASE_CMP_PagingMobile($page, ceil($itemsCount / $rpp), 5);
            $this->addComponent('paging', $paging);
            $first = ($page - 1) * $rpp;
            $count = $rpp;
            $events = array_slice($events, $first, $count);
        }else{
            $events = array();
        }

        $countInfo = OW::getLanguage()->text('frmhashtag', 'able_to_see_text', array('num'=>$itemsCount, 'all'=>$allCount));
        $this->assign('countInfo', $countInfo);
        $events = $eventService->getListingDataWithToolbar($events);
        foreach($events as $key => $item){
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $item['content'])));
            if (isset($stringRenderer->getData()['string'])) {
                $events[$key]['content'] = ($stringRenderer->getData()['string']);
            }
        }
        $this->assign('events', $events);

        if ( sizeof($idList) > sizeof($events) )
        {
            $toolbarArray = array(array('href' => OW::getRouter()->urlForRoute('event.view_event_list', array('list' => 'latest')), 'label' => OW::getLanguage()->text('event', 'view_all_label')));
            $this->assign('toolbar', $toolbarArray);
        }
    }
}