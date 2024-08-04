<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcompetition.controllers
 * @since 1.0
 */
class FRMCOMPETITION_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * @param $params
     */
    public function index($params)
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmcompetition', 'main_menu_item'));
        $this->checkUserAuthority();
        $service = FRMCOMPETITION_BOL_Service::getInstance();

        $competitionForm = $service->getCompetitionForm(OW::getRouter()->urlForRoute('frmcompetition.admin'));
        $this->addForm($competitionForm);

        if (OW::getRequest()->isPost()) {
            if ($competitionForm->isValid($_POST)) {
                $title = $_REQUEST['title'];
                $description = $_REQUEST['description'];
                $active = $_REQUEST['active'];
                if($active == null){
                    $active = false;
                }else if($active == 'on'){
                    $active = true;
                }

                $startDate = $competitionForm->getValues()['startDate'];
                $startDateArray = explode('/', $startDate);
                $startDate = mktime(date('h'), date('i'), date('s'), $startDateArray[1], $startDateArray[2], $startDateArray[0]);

                $endDate = $competitionForm->getValues()['endDate'];
                $endDateArray = explode('/', $endDate);
                $endDate = mktime(date('h'), date('i'), date('s'), $endDateArray[1], $endDateArray[2], $endDateArray[0]);

                $type = $_REQUEST['type'];
                $imageName = $service->saveFile('image');
                $competitionDto = $service->saveCompetition($title, $description, $active, $imageName, $startDate, $endDate, $type);
                if ($competitionForm->getValues()['enSentNotification']==true)
                {
                    $eventIisCompetition = new OW_Event('frmcompetition.on.add.competition', array('competitionDto'=>$competitionDto));
                    OW::getEventManager()->trigger($eventIisCompetition);
                }
                OW::getFeedback()->info(OW::getLanguage()->text('frmcompetition', 'saved_successfully'));
                $this->redirect();
            }
        }
        $page = !empty($_GET['page']) ? $_GET['page'] : 1;
        $count = 20;
        $competitions = $service->findCompetitions(($page-1) * $count, $count);

        $allComptetions = $service->findAllCompetitions();
        $allComptetionsSize = 0;
        if($allComptetions!=null){
            $allComptetionsSize = sizeof($allComptetions);
        }
        $paging = new BASE_CMP_Paging($page, ceil($allComptetionsSize / $count), $count);
        $this->assign('paging', $paging->render());

        $competitionsArray = array();
        foreach ($competitions as $competition) {
            $competitionsInf = array(
                'title' => $competition->title,
                'id' => $competition->id,
                'editUrl' => OW::getRouter()->urlForRoute('frmcompetition.admin.edit.competition', array('competitionId' => $competition->id)),
                'deleteUrl' => "if(confirm('".OW::getLanguage()->text('frmevaluation','delete_item_warning')."')){location.href='" . OW::getRouter()->urlForRoute('frmcompetition.admin.delete.competition', array('competitionId' => $competition->id)) . "';}",
            );

            if ($competition->image != null) {
                $competitionsInf['image'] = $service->getFile($competition->image);
            }

            if($competition->type == $service->TYPE_USER){
                $competitionsInf['valuesUrl'] = OW::getRouter()->urlForRoute('frmcompetition.admin.users', array('competitionId' => $competition->id));
            }else if($competition->type == $service->TYPE_GROUP){
                $competitionsInf['valuesUrl'] = OW::getRouter()->urlForRoute('frmcompetition.admin.groups', array('competitionId' => $competition->id));
            }

            $competitionsArray[] = $competitionsInf;
        }
        $this->assign('competitions', $competitionsArray);
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function editCompetition($params)
    {
        $this->checkUserAuthority();
        $competitionId = null;
        if(isset($params['competitionId']) && is_numeric($params['competitionId'])){
            $competitionId = $params['competitionId'];
        }else{
            throw new Redirect404Exception();
        }

        $this->assign('returnToCompetitionsUrl', OW::getRouter()->urlForRoute('frmcompetition.admin'));

        $service = FRMCOMPETITION_BOL_Service::getInstance();
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmcompetition', 'main_menu_item'));
        $competition = $service->findCompetitionById($competitionId);
        if($competition == null){
            throw new Redirect404Exception();
        }

        $this->assign('competitionTitle', $competition->title);
        if($competition->image != null){
            $this->assign('competitionImageSrc', $service->getFile($competition->image));
        }
        $competitionForm = $service->getCompetitionForm(OW::getRouter()->urlForRoute('frmcompetition.admin.edit.competition', array('competitionId' => $competitionId)), $competition->title, $competition->description, $competition->active, $competition->type, $competition->startDate, $competition->endDate);
        $this->addForm($competitionForm);

        if (OW::getRequest()->isPost()) {
            if ($competitionForm->isValid($_POST)) {
                $title = $_REQUEST['title'];
                $description = $_REQUEST['description'];
                $active = $_REQUEST['active'];
                if($active == null){
                    $active = false;
                }else if($active == 'on'){
                    $active = true;
                }

                $startDate = $competitionForm->getValues()['startDate'];
                $startDateArray = explode('/', $startDate);
                $startDate = mktime(date('h'), date('i'), date('s'), $startDateArray[1], $startDateArray[2], $startDateArray[0]);

                $endDate = $competitionForm->getValues()['endDate'];
                $endDateArray = explode('/', $endDate);
                $endDate = mktime(date('h'), date('i'), date('s'), $endDateArray[1], $endDateArray[2], $endDateArray[0]);

                $type = $_REQUEST['type'];
                $imageName = $service->saveFile('image');
                $old_image = FRMCOMPETITION_BOL_Service::getInstance()->findCompetitionById($competitionId)->image;
                if($imageName == null && $old_image!=null){
                    $imageName =  $old_image;
                }
                $competitionDto = $service->saveCompetition($title, $description, $active, $imageName, $startDate, $endDate, $type, $competitionId);
                if ($competitionForm->getValues()['enSentNotification']==true)
                {
                    $eventIisCompetition = new OW_Event('frmcompetition.on.add.competition', array('competitionDto'=>$competitionDto));
                    OW::getEventManager()->trigger($eventIisCompetition);
                }
                OW::getFeedback()->info(OW::getLanguage()->text('frmcompetition', 'saved_successfully'));
                $this->redirect();
            }
        }
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function deleteCompetition($params)
    {
        $this->checkUserAuthority();
        $competitionId = null;
        if(isset($params['competitionId']) && is_numeric($params['competitionId'])){
            $competitionId = $params['competitionId'];
        }else{
            throw new Redirect404Exception();
        }
        $service = FRMCOMPETITION_BOL_Service::getInstance();
        $service->deleteCompetitionById($competitionId);
        OW::getEventManager()->call('notifications.remove', array(
            'entityType' => 'competition-add_competition',
            'entityId' => $competitionId
        ));
        OW::getEventManager()->call('notifications.remove', array(
            'entityType' => 'competition-add_user_point',
            'entityId' => $competitionId
        ));
        OW::getEventManager()->call('notifications.remove', array(
            'entityType' => 'competition-add_group_point',
            'entityId' => $competitionId
        ));
        $this->redirect(OW::getRouter()->urlForRoute('frmcompetition.admin'));
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function users($params)
    {
        $this->checkUserAuthority();
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmcompetition', 'main_menu_item'));
        $service = FRMCOMPETITION_BOL_Service::getInstance();
        $competitionId = null;
        if(isset($params['competitionId']) && is_numeric($params['competitionId'])){
            $competitionId = $params['competitionId'];
        }else{
            throw new Redirect404Exception();
        }
        $this->assign('returnToCompetitionsUrl', OW::getRouter()->urlForRoute('frmcompetition.admin'));

        $competitionUserForm = $service->getCompetitionUserForm(OW::getRouter()->urlForRoute('frmcompetition.admin.users', array('competitionId' => $competitionId)));
        $this->addForm($competitionUserForm);
        if (OW::getRequest()->isPost()) {
            if ($competitionUserForm->isValid($_POST)) {
                $username = $_REQUEST['username'];
                $user = BOL_UserService::getInstance()->findByUsername($username);
                if($user == null){
                    OW::getFeedback()->error(OW::getLanguage()->text('frmcompetition', 'user_not_found'));
                    $this->redirect();
                }else {
                    $value = $_REQUEST['value'];
                    $service->saveCompetitionUsers($user->getId(), $competitionId, $value);
                    $eventIisCompetition = new OW_Event('frmcompetition.on.add.point.to.user', array('competitionId'=>$competitionId,
                        'userId'=>$user->getId(),'points'=>$value));
                    OW::getEventManager()->trigger($eventIisCompetition);
                    OW::getFeedback()->info(OW::getLanguage()->text('frmcompetition', 'saved_successfully'));
                    $this->redirect();
                }
            }
        }

        $competitionUsers = $service->findCompetitionUsers($competitionId);
        $competitionUsersArray = array();
        foreach ($competitionUsers as $competitionUser) {
            $user = BOL_UserService::getInstance()->findUserById($competitionUser->userId);
            $competitionUserInf = array(
                'username' => $user->username,
                'name' => BOL_UserService::getInstance()->getDisplayName($user->getId()),
                'value' => $competitionUser->value,
                'image' => BOL_AvatarService::getInstance()->getAvatarUrl($user->getId())
            );

            $competitionUsersArray[] = $competitionUserInf;
        }
        $this->assign('competitionUsers', $competitionUsersArray);
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function groups($params)
    {
        $this->checkUserAuthority();
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmcompetition', 'main_menu_item'));
        $service = FRMCOMPETITION_BOL_Service::getInstance();
        $competitionId = null;
        if(isset($params['competitionId']) && is_numeric($params['competitionId'])){
            $competitionId = $params['competitionId'];
        }else{
            throw new Redirect404Exception();
        }
        $this->assign('returnToCompetitionsUrl', OW::getRouter()->urlForRoute('frmcompetition.admin'));

        $competitionUserForm = $service->getCompetitionGroupForm(OW::getRouter()->urlForRoute('frmcompetition.admin.groups', array('competitionId' => $competitionId)));
        $this->addForm($competitionUserForm);
        if (OW::getRequest()->isPost()) {
            if ($competitionUserForm->isValid($_POST)) {
                $groupId = $_REQUEST['groupId'];
                $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
                if($group == null){
                    OW::getFeedback()->error(OW::getLanguage()->text('frmcompetition', 'group_not_found'));
                    $this->redirect();
                }else {
                    $value = $_REQUEST['value'];
                    $service->saveCompetitionGroup($group->getId(), $competitionId, $value);
                    $eventIisCompetition = new OW_Event('frmcompetition.on.add.point.to.group', array('competitionId'=>$competitionId,
                        'groupId'=>$group->getId(),'points'=>$value));
                    OW::getEventManager()->trigger($eventIisCompetition);
                    OW::getFeedback()->info(OW::getLanguage()->text('frmcompetition', 'saved_successfully'));
                    $this->redirect();
                }
            }
        }

        $competitionGroups = $service->findCompetitionGroups($competitionId);
        $competitionGroupsArray = array();
        foreach ($competitionGroups as $competitionGroup) {
            $group = GROUPS_BOL_Service::getInstance()->findGroupById($competitionGroup->groupId);
            $competitionGroupInf = array(
                'title' => $group->title,
                'value' => $competitionGroup->value,
                'image' => GROUPS_BOL_Service::getInstance()->getGroupImageUrl($group)
            );

            $competitionGroupsArray[] = $competitionGroupInf;
        }
        $this->assign('competitionGroups', $competitionGroupsArray);
    }

    public function checkUserAuthority(){
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAdmin()){
            throw new Redirect404Exception();
        }
    }
}