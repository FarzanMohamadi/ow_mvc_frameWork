<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmguidedtour
 * @since 1.0
 */
class FRMGUIDEDTOUR_BOL_UserGuideDao extends OW_BaseDao
{

    const FIRST_TIME = 0;  // When it is the first time that user sees the website
    const UNSEEN = 1;   // When it is not the first time but user haven't seen the guide
    const SEEN = 2;     // When it is not the first time and user has seen the guide

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Singleton instance.
     *
     * @var FRMGUIDEDTOUR_BOL_UserGuideDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMGUIDEDTOUR_BOL_UserGuideDao
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'FRMGUIDEDTOUR_BOL_UserGuide';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmguidedtour_userGuide';
    }

    public function getUserGuideById($id)
    {
        return $this->findById($id);
    }

    public function getIntroductionSeenByUser($userId)
    {
        $userGuide = $this->getUserGuideByUser($userId);
        if (!$userGuide) {
            $userGuide = new FRMGUIDEDTOUR_BOL_UserGuide();
            $userGuide->setUserId($userId);
        }
        $seen = $userGuide->getSeenStatus();
        return $seen;
    }

    public function getUserGuideByUser($userId)
    {
        $example = new OW_Example();
        $example->andFieldEqual("userId", $userId);
        return $this->findObjectByExample($example);
    }

    public function getGuideSeenByUser($userId)
    {
        $userGuide = $this->getUserGuideByUser($userId);
        if (!$userGuide) {
            $userGuide = new FRMGUIDEDTOUR_BOL_UserGuide();
            $userGuide->setUserId($userId);
        }
        $seen = $userGuide->getSeenStatus();
        if($seen == $this::SEEN){
            return true;
        }
        else{
            return false;
        }
    }

    public function setGuideSeenByUser($userId, $seen)
    {
        $userGuide = $this->getUserGuideByUser($userId);
        if (!$userGuide) {
            $userGuide = new FRMGUIDEDTOUR_BOL_UserGuide();
            $userGuide->setUserId($userId);
        }
        if($seen){
            $userGuide->setSeen($this::SEEN);
        }
        else{
            $userGuide->setSeen($this::UNSEEN);
        }

        $this->save($userGuide);
    }

    public function setIntroductionSeenByUser($userId)
    {
        $userGuide = $this->getUserGuideByUser($userId);
        if (!$userGuide) {
            $userGuide = new FRMGUIDEDTOUR_BOL_UserGuide();
            $userGuide->setUserId($userId);
        }
        $userGuide->setSeen($this::UNSEEN);
        $this->save($userGuide);
    }

    public function deleteUserGuideById($id)
    {
        $this->deleteById($id);
    }

    public function deleteUserGuideByExample($userGuide)
    {
        $this->deleteByExample($userGuide);
    }

    public function updateSeenStatus($userId, $status)
    {
        $userGuide = $this->getUserGuideByUser($userId);
        if (!$userGuide) {
            $userGuide = new FRMGUIDEDTOUR_BOL_UserGuide();
            $userGuide->setUserId(OW::getUser()->getId());
        }
        $userGuide->setSeen($status);
        $this->save($userGuide);
    }
}