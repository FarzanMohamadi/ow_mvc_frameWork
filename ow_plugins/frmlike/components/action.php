<?php
class FRMLIKE_CMP_Action extends OW_Component
{
    /**
     * @var array
     */
    private static $data;

    /**
     * @var FRMLIKE_BOL_Service
     */
    private $service;

    /**
     * @var string
     */
    private $entityType;

    /**
     * @var int
     */
    private $entityId;

    /**
     * @var array
     */
    private $statData;

    /**
     * @var int 
     */
    private $myVote;

    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $ownerId;

    /**
     * @var string
     */
    private $parentClass;

    /**
     * Constructor.
     */
    public function __construct( $entityId, $entityType, $statData, $myVote, $ownerId, $parentClass )
    {
        parent::__construct();
        $this->service = FRMLIKE_BOL_Service::getInstance();
        $this->entityId = $entityId;
        $this->entityType = $entityType;
        $this->statData = $statData;
        $this->myVote =false;
        if(isset($myVote->vote))
        {
            $this->myVote=$myVote->vote;
        }
        $this->id = FRMSecurityProvider::generateUniqueId('frmlike-');
        $this->ownerId = (int) $ownerId;
        $this->parentClass = trim($parentClass);

        if ( self::$data === null )
        {
            self::$data = array(
                "loginMessage" =>OW::getLanguage()->text('frmlike','like_login_msg'),
                "ownerMessage" => OW::getLanguage()->text('frmlike','like_owner_msg_new'),
                "likedListLabel" => OW::getLanguage()->text('frmlike','liked_user_list'),
                "dislikedListLabel" => OW::getLanguage()->text('frmlike','disliked_user_list'),
                "totalListLabel" => OW::getLanguage()->text('frmlike','total_user_list'),
                "respondUrl" => OW::getRouter()->urlFor("NEWSFEED_CTRL_Ajax", "action"),
                "currentUserId" => OW::getUser()->isAuthenticated() ? OW::getUser()->getId() : -1,
                "currentUri" => urlencode(OW::getRequest()->getRequestUri())
            );

            OW::getDocument()->addOnloadScript("window.FRMLIKEData = " . json_encode(self::$data) . ";");
        }
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $commonUserList = array_merge((empty($this->statData['upUserId']) ? array() : $this->statData['upUserId']), (empty($this->statData['downUserId']) ? array() : $this->statData['downUserId']));

        $dataToAssign = array(
            "cmpId" => $this->id,
            "userVote" => $this->myVote,
            "entityId" => $this->entityId,
            "entityType" => $this->entityType,
            "ownerId" => $this->ownerId,
            "total" => empty($this->statData["sum"]) ? 0 : intval($this->statData["sum"]),
            "count" => empty($this->statData["count"]) ? 0 : intval($this->statData["count"]),
            "up" => empty($this->statData["up"]) ? 0 : intval($this->statData["up"]),
            "down" => empty($this->statData["down"]) ? 0 : intval($this->statData["down"]),
            "ownerBlock" => (OW::getUser()->isAuthenticated() && OW::getUser()->getId() == $this->ownerId),
            "upUserId" => (!empty($this->statData["upUserId"])) ? $this->statData["upUserId"] : array(),
            "downUserId" => (!empty($this->statData["downUserId"])) ? $this->statData["downUserId"] : array(),
            "commonUserId" => (!empty($commonUserList) ) ? $commonUserList : array(),
            "parentClass" => $this->parentClass,
            "currentUserId" => OW::getUser()->isAuthenticated() ? OW::getUser()->getId() : -1
        );

        $this->assign("data", $dataToAssign);
        OW::getDocument()->addOnloadScript("new FRMLIKE(" . json_encode($dataToAssign) . ");");
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $this->assign('isMobile',true);
        }
        $dislikeActive = (boolean)OW::getConfig()->getValue('frmlike','dislikeActivate');
        $this->assign('dislikeActive',$dislikeActive);

        $isDislikeActivate = $this->isDislikeActivate();
        $this->assign("isDislikeActivate", $isDislikeActivate);
    }

    /**
     * @return bool
     */
    private function isDislikeActivate() {
        $dislikeActive = (boolean)OW::getConfig()->getValue('frmlike','dislikeActivate');
        $dislikePostActivate = (boolean)OW::getConfig()->getValue('frmlike','dislikePostActivate');

        $type = "comment";
        if (strpos($this->entityType, "frmlike") === false) {
            $type = "newsfeed";
        }

        $isDislikeActivate = false;
        if (($type == "comment" && $dislikeActive) || ($type == "newsfeed" && $dislikePostActivate)) {
            $isDislikeActivate = true;
        }
        return $isDislikeActivate;
    }
}
