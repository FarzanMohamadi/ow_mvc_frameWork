<?php
/**
 * frmticketing Service.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmticketing.bol
 * @since 1.0
 */
final class FRMTICKETING_BOL_TicketCategoryUserService
{
    /*
      @var PostDao
    */
    private $userCategoryDao;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        $this->userCategoryDao = FRMTICKETING_BOL_TicketCategoryUserDao::getInstance();
    }

    /**
     * Singleton instance.
     *
     * @var FRMTICKETING_BOL_TicketCategoryUserService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTICKETING_BOL_TicketCategoryUserService
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function save( $dto )
    {
        $dao = $this->userCategoryDao;
        $dao->save($dto);
    }

    public function addUserToCategory($username, $categoryId)
    {
        if(!empty($username) && !empty($categoryId))
        {
            $user = BOL_UserService::getInstance()->findByUsername($username);
            $dto = new FRMTICKETING_BOL_TicketCategoryUser();
            $dto->setUserId($user->getId());
            $dto->setCategoryId($categoryId);
            $this->save($dto);
            return true;
        }
        return false;
    }

    public function deleteByCategoryId($categoryId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('categoryId', $categoryId);
        return $this->userCategoryDao->deleteByExample($ex);
    }

    public function deleteByCategoryIdAndUsername($categoryId, $username)
    {
        $user = BOL_UserService::getInstance()->findByUsername($username);
        $ex = new OW_Example();
        $ex->andFieldEqual('categoryId', $categoryId);
        $ex->andFieldEqual('userId', $user->getId());
        return $this->userCategoryDao->deleteByExample($ex);
    }

    public function findAllCategoryUsersByStatus( $status )
    {
        $activeCategoryUsers = [];
        $results = $this->userCategoryDao->findUsersForCategoriesByStatus($status);
        foreach ($results as $result)
        {
            if(!isset($activeCategoryUsers[$result['categoryId']]['name']))
                $activeCategoryUsers[$result['categoryId']]['name'] = $result['title'];
            $user =  BOL_UserService::getInstance()->findUserById($result['userId']);
            $activeCategoryUsers[$result['categoryId']]['users'][] = $user->getUsername();
        }
        return $activeCategoryUsers;
    }

    public function findUsersOfCategory($categoryId)
    {
        $userIds = array_column($this->userCategoryDao->findUsersOfCategory($categoryId),'userId');
        $users = BOL_UserService::getInstance()->findUserListByIdList($userIds);
        return $users;
    }

    public function findCategoriesOfUser($user)
    {
        return $this->userCategoryDao->findCategoriesOfUser($user);
    }

}