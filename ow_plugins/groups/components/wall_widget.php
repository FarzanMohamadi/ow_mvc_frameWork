<?php
/**
 * Group Wall Widget
 *
 * @package ow_plugins.groups.components
 * @since 1.0
 */
class GROUPS_CMP_WallWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $params = $paramObj->customParamList;

        $commentParams = new BASE_CommentsParams('groups', GROUPS_BOL_Service::ENTITY_TYPE_WAL);

        $groupId = (int) $paramObj->additionalParamList['entityId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        
        $commentParams->setEntityId($groupId);
        $commentParams->setAddComment($group->status == GROUPS_BOL_Group::STATUS_ACTIVE);

        if ( isset($params['comments_count']) )
        {
            $commentParams->setCommentCountOnPage($params['comments_count']);
        }

        if ( isset($params['display_mode']) )
        {
            $commentParams->setDisplayType($params['display_mode']);
        }

        $allowAddComment= GROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId()) !== null;
        $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.load',
            array('groupId'=>$groupId)));
        if ( (isset($channelEvent->getData()['isChannel']) && $channelEvent->getData()['isChannel']==true)){
            $featureComments = array();
            $featureComments['groupId']=$groupId;
            $commentParams->setBatchData($featureComments);
        }         
        $commentParams->setAddComment($allowAddComment);

        $this->addComponent('comments', new BASE_CMP_Comments($commentParams));
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['comments_count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('base', 'cmp_widget_wall_comments_count'),
            'optionList' => array('3' => 3, '5' => 5, '10' => 10, '20' => 20, '50' => 50, '100' => 100),
            'value' => 10
        );

        $settingList['display_mode'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('base', 'cmp_widget_wall_comments_mode'),
            'optionList' => array(
                '1' => OW::getLanguage()->text('base', 'cmp_widget_wall_comments_mode_option_1'),
                '2' => OW::getLanguage()->text('base', 'cmp_widget_wall_comments_mode_option_2')
            ),
            'value' => 2
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_ICON => self::ICON_COMMENT,
            self::SETTING_TITLE => OW::getLanguage()->text('groups', 'wall_widget_label'),
            self::SETTING_WRAP_IN_BOX => false
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}