<?php
/**
 * FRM Oghat widget
 *
 * @since 1.0
 */
class FRMCHALLENGE_CMP_ChallengeWidget extends BASE_CLASS_Widget
{

    /**
     * FRMOGHAT_CMP_ChallengeWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $this->assignList($params);
    }

    private function assignList($params)
    {
        $service = FRMCHALLENGE_BOL_SolitaryService::getInstance();
        $generalService = FRMCHALLENGE_BOL_GeneralService::getInstance();
        $generalService->addStylesAdnScripts();
        OW::getDocument()->addOnloadScript('create_challenge();');
        $createChallengeForm = $generalService->getChallengeCreationForm();
        $this->addForm($createChallengeForm);

        $result = $generalService->getUserChallengesInfo(OW::getUser()->getId());

        /***
         * Solitary challenges
         */
        $solitaryChallengesRequestFinish = new FRMCHALLENGE_CMP_Challenge($result['solitary_request_finish']);
        $solitaryChallengesRequestSelf = new FRMCHALLENGE_CMP_Challenge($result['solitary_request_self']);
        $solitaryChallengesRequestOpponent = new FRMCHALLENGE_CMP_Challenge($result['solitary_request_opponent']);

        $publicSolitary = $service->getPublicSolitaryChallengesInfo(OW::getUser()->getId());
        $challengesRequestPublic = new FRMCHALLENGE_CMP_Challenge($publicSolitary);

        $this->addComponent('solitary_request_finish', $solitaryChallengesRequestFinish);
        $this->addComponent('solitary_request_self', $solitaryChallengesRequestSelf);
        $this->addComponent('solitary_request_opponent', $solitaryChallengesRequestOpponent);
        $this->addComponent('solitary_request_public', $challengesRequestPublic);

        $this->assign('solitary_request_finish_count', sizeof($result['solitary_request_finish']));
        $this->assign('solitary_request_self_count', sizeof($result['solitary_request_self']));
        $this->assign('solitary_request_opponent_count', sizeof($result['solitary_request_opponent']));
        $this->assign('solitary_request_public_count', sizeof($publicSolitary));
        /***
         * End Solitary challenges
         */

        /***
         * Universal challenges
         */

        $universalChallengesRequestFinish = new FRMCHALLENGE_CMP_Challenge($result['universal_request_finish']);
        $universalChallengesRequestSelf = new FRMCHALLENGE_CMP_Challenge($result['universal_request_self']);
        $universalChallengesRequestPublic = new FRMCHALLENGE_CMP_Challenge($result['universal_request_public']);

        $this->addComponent('universal_request_finish', $universalChallengesRequestFinish);
        $this->addComponent('universal_request_self', $universalChallengesRequestSelf);
        $this->addComponent('universal_request_public', $universalChallengesRequestPublic);

        $this->assign('universal_request_finish_count', sizeof($result['universal_request_finish']));
        $this->assign('universal_request_self_count', sizeof($result['universal_request_self']));
        $this->assign('universal_request_public_count', sizeof($result['universal_request_public']));
        /***
         * End Universal challenges
         */

        /***
         * Users point
         */
        $usersInfo = $generalService->getUsersPointInfo(10, OW::getUser()->getId());
        $this->assign('usersInfo', $usersInfo);
        /***
         * End Users point
         */

        $canCreate = $generalService->canUserCreateChallenge();
        $this->assign('canCreate', $canCreate);

        $solitaryEnable = false;
        if($generalService->isSolitaryChallengeEnable()){
            $solitaryEnable = true;
        }
        $this->assign('solitaryEnable', $solitaryEnable);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmchallenge', 'main_menu_item'),
            self::SETTING_ICON => self::ICON_APP
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}