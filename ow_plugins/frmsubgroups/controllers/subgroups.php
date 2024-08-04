<?php
class FRMSUBGROUPS_CTRL_Subgroups extends OW_ActionController
{

    /**
     *
     * @var FRMSUBGROUPS_BOL_Service
     */
    private $service;

    public function __construct()
    {
        $this->service = FRMSUBGROUPS_BOL_Service::getInstance();

    }

    /**
     * @param $params
     * @throws Redirect404Exceptionاجازه درج نظر برای نمایه
     */
    public function subgroupList($params)
    {
        $searchTitle='';

        if (OW::getRequest()->isPost()) {
            $searchTitle = $_POST['searchTitle'];
        }

        if(isset($_GET['searchTitle'])){
            $searchTitle = $_GET['searchTitle'];
        }

        $parentGroupId = (int) $params['groupId'];
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($parentGroupId);

        if ( $groupDto === null )
        {
            throw new Redirect404Exception();
        }

        $language = OW::getLanguage();

        if ( !GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($groupDto) )
        {
            throw new Redirect404Exception();
        }

        $eventHasViewAccess=OW::getEventManager()->trigger(new OW_Event('frmsubgroup.check.access.view.subgroups',array('groupId'=>$parentGroupId)));
        $canView=$eventHasViewAccess->getData()['canView'];
        if(!isset($canView) || !$canView)
        {
            throw new Redirect404Exception();
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        $first = ($page - 1) * $perPage;
        $count = $perPage;


        $subGroupsDto =  FRMSUBGROUPS_BOL_Service::getInstance()->findSubGROUPSByParentGroup($parentGroupId,$searchTitle,$first,$count);
        $subGroupsCount = FRMSUBGROUPS_BOL_Service::getInstance()->findSubGROUPSByParentGroupCount($parentGroupId,$searchTitle);

        $paging = new BASE_CMP_Paging($page, ceil($subGroupsCount / $perPage), 2);
        $this->displayGroupList($parentGroupId,$subGroupsDto, $paging);
        $subGroupFilterForm = $this->service->getGroupFilterForm($searchTitle);
        $this->addForm($subGroupFilterForm);
        $plugin = OW::getPluginManager()->getPlugin('frmsubgroups');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'frmsubgroups.js');
        if( FRMSecurityProvider::checkPluginActive('groups', true) && GROUPS_CMP_BriefInfoWidget::userAllowedAccess()){
            $this->addComponent('groupBriefInfo', new GROUPS_CMP_BriefInfo($parentGroupId));
        }
    }

    private function displayGroupList( $parentGroupId, $subGroupsDto, $paging )
    {
        $templatePath = OW::getPluginManager()->getPlugin('frmsubgroups')->getCtrlViewDir() . 'subgroups_list.html';
        $this->setTemplate($templatePath);

        $out = array();

        foreach ( $subGroupsDto as $subGroup )
        {
            /* @var $subGroup GROUPS_BOL_Group */

            $userCount = GROUPS_BOL_Service::getInstance()->findUserListCount($subGroup->id);
            $title = strip_tags($subGroup->title);

            $toolbar = array(
                array(
                    'label' => OW::getLanguage()->text('groups', 'listing_users_label', array(
                        'count' => $userCount
                    ))
                )
            );
            $sentenceCorrected = false;
            if ( mb_strlen($subGroup->description) > 300 )
            {
                $sentence = strip_tags($subGroup->description);
                $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_HALF_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
                if(isset($event->getData()['correctedSentence'])){
                    $sentence = $event->getData()['correctedSentence'];
                    $sentenceCorrected = true;
                }
                $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
                if(isset($event->getData()['correctedSentence'])){
                    $sentence = $event->getData()['correctedSentence'];
                    $sentenceCorrected = true;
                }
            }
            if($sentenceCorrected){
                $content = $sentence.'...';
            }
            else{
                $content = UTIL_String::truncate(strip_tags($subGroup->description), 300, "...");
            }
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $content)));
            if (isset($stringRenderer->getData()['string'])) {
                $content = ($stringRenderer->getData()['string']);
            }

            $imageSource = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($subGroup);
            $out[] = array(
                'id' => $subGroup->id,
                'url' => OW::getRouter()->urlForRoute('groups-view', array('groupId' => $subGroup->id)),
                'title' => $title,
                'imageTitle' => $title,
                'content' => $content,
                'time' => UTIL_DateTime::formatDate($subGroup->timeStamp),
                'imageSrc' => $imageSource,
                'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo($subGroup->id, $imageSource),
                'users' => $userCount,
                'toolbar' => $toolbar,
                'unreadCount' => GROUPS_BOL_Service::getInstance()->getUnreadCountForGroupUser($subGroup->id)
            );
        }

        $this->addComponent('paging', $paging);

        $canCreate=false;
        $eventHasAccess=OW::getEventManager()->trigger(new OW_Event('frmsubgroup.check.access.create.subgroups',array('groupId'=>$parentGroupId)));
        if(isset($eventHasAccess->getData()['canCreateSubGroup']) && $eventHasAccess->getData()['canCreateSubGroup'])
        {
            $canCreate=true;
            $createSubgroupLink = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('groups-create'),array('parentGroupId'=>$parentGroupId));
            $this->assign('createSubgroupLink',$createSubgroupLink);
        }
        $originalUrl =$url= OW::getRouter()->urlForRoute('frmsubgroups.group-list',['groupId'=>$parentGroupId]);
        $this->assign('originalUrl',$originalUrl);
        $this->assign('canCreate', $canCreate);
        $this->assign('list', $out);
        $this->setDocumentKey("user_groups");
    }

}