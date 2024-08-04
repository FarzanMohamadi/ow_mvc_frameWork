<?php
class FRMCOMPETITION_CTRL_Competition extends OW_ActionController
{

    public function index($params)
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmcompetition', 'main_menu_item'));
        $service = FRMCOMPETITION_BOL_Service::getInstance();

        $page = !empty($_GET['page']) ? $_GET['page'] : 1;
        $count = 20;
        $competitions = $service->findCompetitions(($page-1) * $count, $count);

        $allCompetitions = $service->findAllCompetitions();
        $allCompetitionsSize = 0;
        if($allCompetitions!=null){
            $allCompetitionsSize = sizeof($allCompetitions);
        }
        $paging = new BASE_CMP_Paging($page, ceil($allCompetitionsSize / $count), $count);
        $this->assign('paging', $paging->render());

        $competitionsArray = array();
        foreach ($competitions as $competition) {
            $competitionsInf = array(
                'title' => $competition->title,
                'imageTitle' => $competition->title,
                'id' => $competition->id,
                'content' => $service->getPartialDescription($competition->description)
            );
            $defaultImageSrc = OW::getPluginManager()->getPlugin("frmcompetition")->getStaticUrl().'img/default.png';
            if ($competition->image != null) {
                $competitionsInf['imageSrc'] = $service->getFile($competition->image);
            }else{
                $competitionsInf['imageSrc'] = $defaultImageSrc;
            }

//            $sizeOfParticipant = 0;
//            if($competition->type == $service->TYPE_GROUP) {
//                $sizeOfParticipant = sizeof($service->findCompetitionGroups($competition->id));
//            }else if($competition->type == $service->TYPE_USER) {
//                $sizeOfParticipant = sizeof($service->findCompetitionUsers($competition->id));
//            }

            $competitionsInf['infoString'] = '<a href="'.OW::getRouter()->urlForRoute('frmcompetition.competition', array('id' => $competition->id)).'">'.$competition->title.'</a>';
            if(!$competition->active){
                $competitionsInf['addClass'] = 'not_active ow_smallmargin';
                $competitionsInf['infoString'] = $competitionsInf['infoString'] . ' (' . OW::getLanguage()->text('frmcompetition', 'not_active') . ')';
            }else{
                $competitionsInf['addClass'] = 'ow_smallmargin';
            }

            $competitionsInf['toolbar'][] = array('label' => OW::getLanguage()->text('frmcompetition', 'endDate') . ': ' . UTIL_DateTime::formatSimpleDate($competition->endDate, true), 'class' => 'competition_end_date ow_ipc_date');
            $competitionsInf['toolbar'][] = array('label' => OW::getLanguage()->text('frmcompetition', 'startDate') . ': ' . UTIL_DateTime::formatSimpleDate($competition->startDate, true), 'class' => 'competition_start_date ow_ipc_date');

//            $competitionsInf['sizeOfParticipant'] = $sizeOfParticipant;

            $competitionsArray[] = $competitionsInf;
        }
        $this->assign('competitions', $competitionsArray);
        $cssDir = OW::getPluginManager()->getPlugin("frmcompetition")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "frmcompetition.css");
    }

    public function viewCompetition($params){
        $service = FRMCOMPETITION_BOL_Service::getInstance();
        $competitionId = null;
        if(isset($params['id']) && is_numeric($params['id'])){
            $competitionId = $params['id'];
        }else{
            throw new Redirect404Exception();
        }

        $this->assign('returnToCompetitionsUrl', OW::getRouter()->urlForRoute('frmcompetition.index'));
        $competition = $service->findCompetitionById($competitionId);
        if($competition==null){
            throw new Redirect404Exception();
        }
        OW::getDocument()->setTitle($competition->title);

        $competitionItemsArray = array();
        $titleOfValues = 'competition_groups_values';
        $sizeOfParticipant = 0;
        if($competition->type == $service->TYPE_GROUP) {
            $competitionGroups = $service->findCompetitionGroups($competitionId);
            $sizeOfParticipant = sizeof($competitionGroups);
            foreach ($competitionGroups as $competitionGroup) {
                $group = GROUPS_BOL_Service::getInstance()->findGroupById($competitionGroup->groupId);
                $competitionGroupInf = array(
                    'infoString' => '<a href="'.OW::getRouter()->urlForRoute('groups-view', array('groupId' => $group->id)).'">'.$group->title.'</a>',
                    'content' => OW::getLanguage()->text('frmcompetition', 'value') . ': ' . $competitionGroup->value,
                    'imageTitle' => $group->title,
                    'imageSrc' => GROUPS_BOL_Service::getInstance()->getGroupImageUrl($group)
                );

                $competitionItemsArray[] = $competitionGroupInf;
            }
        }else if($competition->type == $service->TYPE_USER) {
            $competitionUsers = $service->findCompetitionUsers($competitionId);
            $sizeOfParticipant = sizeof($competitionUsers);
            foreach ($competitionUsers as $competitionUser) {
                $user = BOL_UserService::getInstance()->findUserById($competitionUser->userId);
                $displayName = BOL_UserService::getInstance()->getDisplayName($user->getId());
                $avatarSrc = BOL_AvatarService::getInstance()->getAvatarUrl($user->getId());
                $userProfileUrl = OW::getRouter()->urlForRoute('base_user_profile', array('username' => $user->username));
                $competitionUserInf = array(
                    'infoString' => '<a href="'.$userProfileUrl.'">'.$displayName.'</a>',
                    'imageSrc' => $avatarSrc,
                    'imageTitle' => $displayName,
                    'content' => OW::getLanguage()->text('frmcompetition', 'value') . ': ' . $competitionUser->value
                );

                $competitionItemsArray[] = $competitionUserInf;
            }
            $titleOfValues = 'competition_users_values';
        }
        $competitionInfo = array(
            'title' => $competition->title,
            'description' => $competition->description,
            'startDate' => UTIL_DateTime::formatSimpleDate($competition->startDate, true),
            'endDate' => UTIL_DateTime::formatSimpleDate($competition->endDate, true),
            'active' => $competition->active
        );

        $defaultImageSrc = OW::getPluginManager()->getPlugin("frmcompetition")->getStaticUrl().'img/default.png';
        if ($competition->image != null) {
            $competitionInfo['image'] = $service->getFile($competition->image);
        }else{
            $competitionInfo['image'] = $defaultImageSrc;
        }

        if(!$competition->active){
            $this->assign('notActive', true);
        }
        $this->assign('sizeOfParticipant', $sizeOfParticipant);
        $this->assign('competition', $competitionInfo);
        $this->assign('titleOfValues', OW::getLanguage()->text('frmcompetition', $titleOfValues));
        $this->assign('competitionItems', $competitionItemsArray);
        $cssDir = OW::getPluginManager()->getPlugin("frmcompetition")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "frmcompetition.css");
    }
}