<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class PASSWORDSECURITY_BOL_Service
{
    private static $classInstance;
    private $passwordSecurityDao;
    
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }
        
        return self::$classInstance;
    }
    
    private function __construct()
    {
        $this->passwordSecurityDao = PASSWORDSECURITY_BOL_PasswordSecurityDao::getInstance();
    }
    
    /***
     * @param $userId
     * @param $password
     * @param $sections
     * @return false|PASSWORDSECURITY_BOL_PasswordSecurity
     */
    public function createPasswordSecurity($userId , $password , $sections)
    {
        //create password on the in-app sections for user
        
        $hash_password = $this->hashPassword($userId , $password);
        $sections_list_json = $this->getSectionsListJson($sections);
    
        return $this->passwordSecurityDao->createPassword($userId,$hash_password,$sections_list_json);
        
    }
    
    /***
     * @param $userId
     * @param $password
     * @return array|string
     */
    private function hashPassword($userId , $password)
    {
        return BOL_UserService::getInstance()->hashPassword($password , $userId);
    }
    
    /***
     * @param $list
     * @return false|string
     */
    private function getSectionsListJson($list)
    {
        //standard index 0,1,2... create problem in json_decode.should be to create disorder
        
        if (array_key_exists(0 , $list))
        {
            $list[sizeof($list)] = $list[0];
            unset($list[0]);
        }
        return json_encode($list);
    }
    
    /***
     * @param $userId
     * @return PASSWORDSECURITY_BOL_PasswordSecurity|string|null
     */
    public function getUserPasswordSecurity($userId)
    {
        return $this->passwordSecurityDao->findUserPasswordSecurity($userId);
    }
    
    /***
     * @param $userId
     * @return mixed
     */
    public function isActivePasswordSecurity($userId)
    {
        $user = $this->getUserPasswordSecurity($userId);
    
        if ($user)
            return $user->isActive ;
        else
            return null;
    }
    
    /***
     * @param $userId
     * @param $activate
     * @return void
     */
    public function changeActivatePasswordSecurity($userId , $activate)
    {
        if ($activate)
            $this->passwordSecurityDao->activatePasswordSecurity($userId , $activate);
        else
            $this->passwordSecurityDao->deActivatePasswordSecurity($userId , $activate);
        
    }
    
    /***
     * @param $userId
     * @param $password
     * @return void
     */
    public function updatePassword($userId , $password)
    {
        $new_password = $this->hashPassword($userId , $password);
        $this->passwordSecurityDao->updatePassword($userId , $new_password);
    }
    
    /***
     * @param $userId
     * @param $sections
     * @return void
     */
    public function setSectionsList($userId , $sections)
    {
    
        //set list of in-app section that require password
        
        $sections_list_json = $this->getSectionsListJson($sections);
        
        $this->passwordSecurityDao->setSectionsList($userId ,$sections_list_json);
        
    }
    
    /***
     * @param $userId
     * @return mixed
     */
    public function getSectionsList($userId)
    {
        //get list of in-app section that require password
        
        $user = $this->getUserPasswordSecurity($userId);
        return json_decode($user->sectionsList,true);
    }
    
    /***
     * @param $userId
     * @param $section
     * @return bool
     */
    public function isSectionInList($userId , $section)
    {
        //check if in-app section that require password
        $user = $this->getUserPasswordSecurity($userId);
        $list = json_decode($user->sectionsList , true);
        return in_array($section , $list);
        
    }
    
    /***
     * @param $section
     * @return array
     */
    public function findUsersBySection($section)
    {
        //list of user who set password for a section
        $usersId = [];
        $list = $this->passwordSecurityDao->findUsersBySection($section);
        foreach ($list as $user)
            $usersId[] = $user->userId;
        
        return $usersId;
    }
    
    /***
     * @param $userId
     * @param $input_password
     * @return bool
     */
    public function checkUserPassword( $userId, $input_password )
    {
        
        $user = $this->getUserPasswordSecurity($userId);
        
        if ( $input_password === null || $user === null )
        {
            return false;
        }
        
        $password = $this->hashPassword($userId , $input_password);
        
        if ( $user->password === $password )
        {
            return true;
        }
        
        return false;
    }
    
    /***
     * @param OW_Event $event
     * @return void
     */
    public function onPasswordNeededSection(OW_Event $event)
    {
        //response to a triggerred event before access a section of app.
        //and check if section that require password
        
        $params = $event->getParams();
        
        if (!(isset($params['userId']) && isset($params['section'])))
        {
            $event->setData(array('params'=>false));
    
            return ;
        }
    
        $userId = $params['userId'];
        $section = $params['section'];
    
    
    
        $user = $this->getUserPasswordSecurity($userId);
        if ($user)
        {
            if ($user->isActive)
            {
                $list = json_decode($user->sectionsList , true);
                
                if (in_array($section , $list))
                {
                    if (!isset($params['password']))
                    {
                        $event->setData(array('params'=>false));
                        return ;
    
                    }
                    $password = $params['password'];
    
                    
                    if ($user->password === $this->hashPassword($userId , $password))
                    {
                        $event->setData(array('activePasswordSecurity'=>true,'validPasswordSecurity'=>true));
                        return ;
    
                    }
                    else
                    {
                        $event->setData(array('activePasswordSecurity'=>true,'validPasswordSecurity'=>false));
                        return ;
    
                    }
                
                }
            }
        }
    
        $event->setData(array('activePasswordSecurity'=>false));
    
    }
    
}
