<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsecurityessentials.controllers
 * @since 1.0
 */
class FRMSECURITYESSENTIALS_CTRL_Iissecurityessentials extends OW_ActionController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param null $params
     */
    public function editPrivacy($params = NULL)
    {
        $privacy = $_REQUEST['privacy'];
        $objectId = $_REQUEST['objectId'];
        $actionType = $_REQUEST['actionType'];
        $feedId = $_REQUEST['feedId'];
        $res = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->editPrivacyProcess($privacy, $objectId, $actionType, $feedId);

        if(isset($res['result'])) {
            if($res['result']){
                exit(json_encode(array('result' => true,
                    'title' => $res['title'],
                    'id' => $res['id'],
                    'src' => $res['src'],
                    'privacy' => $res['privacy'],
                    'privacy_list' => $res['privacy_list'])));
            }else{
                exit(json_encode(array('result' => false)));
            }
        }else{
            exit(json_encode(array('result' => false)));
        }
    }

    public function deleteFeedItem($params = null){
            if(!isset($_GET['code'])){
                throw new Redirect404Exception();
            }
            $code = $_GET['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_activity')));

        FRMSECURITYESSENTIALS_BOL_Service::getInstance()->deleteFeedItemByActivityId($params['activityId']);
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param null $params
     * @throws Redirect404Exception
     */
    public function deleteUser($params = null){
        $service = FRMSECURITYESSENTIALS_BOL_Service::getInstance();
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmsecurityessentials', 'delete_user_heading'));
        $this->setPageTitle($language->text('frmsecurityessentials', 'delete_user_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
        $result=$service->deleteUser($params);
        if(isset($result['redirect404Error']))
        {
            throw new Redirect404Exception();
        }

        foreach ($result['component'] as $key => $value)
        {
            $this->addComponent($key,$value);
            $deleting_user_profile_url = $value->assignedVars['userData']['url'];
        }
        foreach ($result['assign'] as $key => $value)
        {
            $this->assign($key,$value);
        }
        $this->assign('deleting_user_profile_url', isset($deleting_user_profile_url) ? $deleting_user_profile_url : null);
        $this->addForm($result['form']);

        if(isset($result['error']))
        {
            OW::getFeedback()->error($language->text('base', 'password_protection_error_message'));
        }

        if(isset($result['success']))
        {
            OW::getFeedback()->info($language->text('frmsecurityessentials', 'users_deleted_successfully'));
            $this->redirect(OW::getRouter()->urlForRoute('base_index'));
        }
        $this->setDocumentKey('user_delete');
    }
}

