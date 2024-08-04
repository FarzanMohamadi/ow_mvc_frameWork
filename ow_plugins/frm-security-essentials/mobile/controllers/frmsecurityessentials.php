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
class FRMSECURITYESSENTIALS_MCTRL_Iissecurityessentials extends OW_MobileActionController
{
    public function __construct()
    {
        parent::__construct();
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
        }
        foreach ($result['assign'] as $key => $value)
        {
            $this->assign($key,$value);
        }

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
    }
}

