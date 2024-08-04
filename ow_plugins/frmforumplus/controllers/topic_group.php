<?php
/**
 * Class FORUM_CTRL_Topic
 */
class FRMFORUMPLUS_CTRL_TopicGroup extends OW_ActionController
{
    private $frmForumPlusService;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->frmForumPlusService = FRMFORUMPLUS_BOL_Service::getInstance();

    }


    public function addGroupForumTopicToWidget( array $params )
    {
        if(!isset($params['groupId']))
        {
            throw new Redirect404Exception();
        }
        $code = $_GET['code'];
        if(!isset($code)){
            throw new Redirect404Exception();
        }
        OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
            array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'add_group_topic_widget')));
        $groupId = $params['groupId'];
        $config = OW::getConfig();
        if(!$config->configExists('frmforumplus','selected_groups_forums'))
        {
            $selectedGroupIds = array($groupId);
            $config->addConfig('frmforumplus', 'selected_groups_forums',   json_encode($selectedGroupIds));
        }else{
            $selectedGroupIds  = json_decode($config->getValue('frmforumplus','selected_groups_forums'),true);
            if ( !in_array($groupId,$selectedGroupIds) ){
                array_push($selectedGroupIds,$groupId);
                $config->saveConfig('frmforumplus', 'selected_groups_forums',   json_encode($selectedGroupIds));
            }
        }
        OW::getFeedback()->info(OW::getLanguage()->text('frmforumplus', 'add_success_msg'));
        $this->redirect(OW::getRouter()->urlForRoute('groups-view', array('groupId' => $groupId)));
    }

    public function removeGroupForumTopicToWidget( array $params )
    {
        if(!isset($params['groupId']))
        {
            throw new Redirect404Exception();
        }
        $code = $_GET['code'];
        if(!isset($code)){
            throw new Redirect404Exception();
        }
        OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
            array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'remove_group_topic_widget')));
        $groupId = $params['groupId'];
        $config = OW::getConfig();
        if(!$config->configExists('frmforumplus','selected_groups_forums'))
        {

        }else{
            $selectedGroupIds  = json_decode($config->getValue('frmforumplus','selected_groups_forums'),true);
            if (($key = array_search($groupId, $selectedGroupIds)) !== false) {
                unset($selectedGroupIds[$key]);
                $config->saveConfig('frmforumplus', 'selected_groups_forums',   json_encode($selectedGroupIds));
            }
        }
        OW::getFeedback()->info(OW::getLanguage()->text('frmforumplus', 'remove_success_msg'));
        $this->redirect(OW::getRouter()->urlForRoute('groups-view', array('groupId' => $groupId)));
    }
}
