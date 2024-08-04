<?php
class FRMSUBGROUPS_CMP_SubgroupListWidget extends BASE_CLASS_Widget
{

    /**
     * FRMSUBGROUPS_CMP_SubgroupListWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {

        $groupId = $params->additionalParamList['entityId'];
        $count = ( empty($params->customParamList['count']) ) ? 10 : (int) $params->customParamList['count'];
        $eventHasViewAccess=OW::getEventManager()->trigger(new OW_Event('frmsubgroup.check.access.view.subgroups',array('groupId'=>$groupId)));
        $canView=$eventHasViewAccess->getData()['canView'];
        if(!isset($canView) || !$canView)
        {
            $this->assign('accessDenied',true);
        }else{
            $this->assignList($groupId, $count);
        }
    }

    private function assignList( $groupId, $count )
    {
        $truncateLength = 100;
        $subGroupsDto =  FRMSUBGROUPS_BOL_Service::getInstance()->findSubGROUPSByParentGroup($groupId,null,0,$count);
        $totalSubGroups = FRMSUBGROUPS_BOL_Service::getInstance()->findSubGROUPSByParentGroupCount($groupId,null);
        $subGroupList = array();
        foreach ( $subGroupsDto as $group )
        {
            $sentenceCorrected = false;
            if ( mb_strlen($group->title) > 100 )
            {
                $sentence = $group->title;
                $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_HALF_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 100)));
                if(isset($event->getData()['correctedSentence'])){
                    $sentence = $event->getData()['correctedSentence'];
                    $sentenceCorrected=true;
                }
                $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 100)));
                if(isset($event->getData()['correctedSentence'])){
                    $sentence = $event->getData()['correctedSentence'];
                    $sentenceCorrected=true;
                }
            }
            if($sentenceCorrected){
                if(mb_strlen($sentence)>=$truncateLength-3){
                    $groupTitle = UTIL_String::truncate($group->title, $truncateLength-3, '...');
                }else{
                    $groupTitle = $sentence.'...';
                }
            }
            else{
                $groupTitle = UTIL_String::truncate($group->title, $truncateLength-3, '...');
            }


            $groupImage = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($group);
            $subGroupList[$group->id]['url'] = OW::getRouter()->urlForRoute('groups-view',['groupId'=>$group->id]);
            $subGroupList[$group->id]['title'] = $groupTitle;
            $subGroupList[$group->id]['groupId'] = $group->id;
            $subGroupList[$group->id]['imageUrl'] =  $groupImage;
            $subGroupList[$group->id]['imageInfo'] = BOL_AvatarService::getInstance()->getAvatarInfo($group->id, $groupImage);
        }

        $eventHasAccess=OW::getEventManager()->trigger(new OW_Event('frmsubgroup.check.access.create.subgroups',array('groupId'=>$groupId)));
        if(isset($eventHasAccess->getData()['canCreateSubGroup']) && $eventHasAccess->getData()['canCreateSubGroup']){
            $createSubgroupLink = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('groups-create'),array('parentGroupId'=>$groupId));
            $this->assign('createSubgroupLink',$createSubgroupLink);
            $this->assign("showCreate", true);
        }


        $this->assign("subgroupList", $subGroupList);
        $this->assign("subgroupsCount", $totalSubGroups);
        $this->assign('view_all_subGroups', OW::getRouter()->urlForRoute('frmsubgroups.group-list', array('groupId' => $groupId)));
        $this->assign("groupId", $groupId);
        return !empty($filelist);
    }


    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW_Language::getInstance()->text('frmsubgroups', 'subgroup_list_settings_count'),
            'value' => 10
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmsubgroups', 'subgroup_list_title'),
            self::SETTING_ICON => self::ICON_FILE
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}