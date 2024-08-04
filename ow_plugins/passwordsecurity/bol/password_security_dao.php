<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class  PASSWORDSECURITY_BOL_PasswordSecurityDao extends OW_BaseDao
{
    
    const USER_ID = 'userId';
    const IS_ACTIVE = 'isActive';
    const PASSWORD = 'password';
    const LAST_UPDATE = 'lastUpdate';
    const SECTIONS_LIST = 'sectionsList';
    
    
    
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'PASSWORDSECURITY_BOL_PasswordSecurity';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'passwordsecurity';
    }
    
    public function createPassword($userId , $password , $sections)
    {
        $user = $this->findUserPasswordSecurity($userId);
    
        if (!$user)
        {
            $user = new PASSWORDSECURITY_BOL_PasswordSecurity();
            $user->userId = $userId;
            $user->password = trim($password);
            $user->isActive = true ;
            $user->sectionsList = $sections;
            $user->lastUpdate = (int) time();
            
            $this->save($user);
            return $user ;
            
        }
        else
        {
            return false;
        }
    }

    public function findUserPasswordSecurity($userId){
        $ex = new OW_Example();
        $ex->andFieldEqual(self::USER_ID, $userId);
        return $this->findObjectByExample($ex);
    }
    
    public function findActiveUsers($userIds=null)
    {
        $ex = new OW_Example();
        if (!empty($userIds))
        {
            $ex->andFieldInArray('userId' , $userIds);
        }
        $ex->andFieldEqual(self::IS_ACTIVE, true);
        return $this->findListByExample($ex);
    }
    
    public function findDeactiveUsers($userIds=null)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual(self::IS_ACTIVE, false);
        if (!empty($userIds))
        {
            $ex->andFieldInArray('userId' , $userIds);
        }
        return $this->findListByExample($ex);
    }
    
    public function findUsersBySection($section)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual(self::IS_ACTIVE, true);
        $ex->andFieldLike(self::SECTIONS_LIST , '%'. $section . '%');
        return $this->findListByExample($ex);
        
    }

    
    public function activatePasswordSecurity($userId)
    {
        $user = $this->findUserPasswordSecurity($userId);
        $user->isActive = true;
        $user->lastUpdate = time();
        $this->save($user);
        
    }
    
    public function deActivatePasswordSecurity($userId)
    {
        $user = $this->findUserPasswordSecurity($userId);
        $user->isActive = false;
        $user->lastUpdate = time();
        $this->save($user);
    }
    
    public function updatePassword($userId , $newPassword)
    {
        $user = $this->findUserPasswordSecurity($userId);
        $user->password = $newPassword;
        $user->lastUpdate = time();
        $this->save($user);
    }
    
    public function setSectionsList($userId , $sections)
    {
        $user = $this->findUserPasswordSecurity($userId);
        $user->sectionsList = $sections;
        $user->lastUpdate = time();
        $this->save($user);
        
    }

    
}
