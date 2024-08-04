<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_BOL_WebServicePasswordsecurity
{
    private static $classInstance;
    
    private $passwordSecurityService;
    
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }
        
        return self::$classInstance;
    }
    
    private function __construct()
    {
    }
    
    
    public function createPassword()
    {
        
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        
        $userId = OW::getUser()->getId();
        
        if (!isset($_POST['password']) || !isset($_POST['passwordConfirm'])) {
            return array('valid' => false, 'message' => 'password and confirm is require');
            
        }
        
        if (!isset($_POST['sections'])) {
            return array('valid' => false, 'message' => 'sections is require');
            
        }
        
        if ($_POST['password'] != $_POST['passwordConfirm']) {
            return array('valid' => false, 'message' => 'password and confirm is not equal');
            
        }
        
        $sections = explode(',', $_POST['sections']);
        
        $user_password = $this->passwordSecurityService->createPasswordSecurity($userId, $_POST['password'], $sections);
        
        if ($user_password)
            return array('valid' => true, 'message' => 'password create');
        
        else
            return array('valid' => false, 'message' => 'user password already exist');
        
    }
    
    public function changePassword()
    {
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();
        
        if (!isset($_POST['oldPassword']) || !isset($_POST['newPassword']) || !isset($_POST['newPasswordConfirm'])) {
            return array('valid' => false, 'message' => 'oldPassword and newPassword and newPasswordConfirm is require');
        }
        
        if ($_POST['newPassword'] != $_POST['newPasswordConfirm']) {
            return array('valid' => false, 'message' => 'newPassword and newPasswordConfirm is not equal');
        }
        
        $old_password_status = $this->passwordSecurityService->checkUserPassword($userId, $_POST['oldPassword']);
        
        if ($old_password_status) {
            $this->passwordSecurityService->updatePassword($userId, $_POST['newPassword']);
            
            return array('valid' => true, 'message' => 'password updated');
        } else
            return array('valid' => false, 'message' => 'make sure oldPassword exists and correct');
        
        
    }
    
    public function checkPassword()
    {
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();
        if (!isset($_POST['password'])) {
            return array('valid' => false, 'message' => 'password is require');
        }
        
        $status = $this->passwordSecurityService->checkUserPassword($userId, $_POST['password']);
        
        if ($status)
            return array('valid' => true, 'message' => 'password correct');
        else
            return array('valid' => false, 'message' => 'make sure password exists and correct');
        
    }
    
    public function activePassword()
    {
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();
        
        if (!isset($_POST['password'])) {
            return array('valid' => false, 'message' => 'password is require');
        }
        
        $status = $this->passwordSecurityService->checkUserPassword($userId, $_POST['password']);
        
        if ($status) {
            $this->passwordSecurityService->changeActivatePasswordSecurity($userId, true);
            return array('valid' => true, 'message' => 'password activate');
        } else
            return array('valid' => false, 'message' => 'make sure password exists and correct');
        
        
    }
    
    public function deactivePassword()
    {
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();
        
        if (!isset($_POST['password'])) {
            return array('valid' => false, 'message' => 'password is require');
        }
        
        $status = $this->passwordSecurityService->checkUserPassword($userId, $_POST['password']);
        
        if ($status) {
            $this->passwordSecurityService->changeActivatePasswordSecurity($userId, false);
            return array('valid' => true, 'message' => 'password deactivate');
        } else
            return array('valid' => false, 'message' => 'make sure password exists and correct');
        
    }
    
    public function updateSections()
    {
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();
        
        if (!isset($_POST['password'])) {
            return array('valid' => false, 'message' => 'password is require');
        }
        
        if (!isset($_POST['sections'])) {
            return array('valid' => false, 'message' => 'sections is require');
            
        }
        
        $status = $this->passwordSecurityService->checkUserPassword($userId, $_POST['password']);
        
        if ($status) {
            $sections = explode(',', $_POST['sections']);
            $this->passwordSecurityService->setSectionsList($userId, $sections);
            
            return array('valid' => true, 'message' => 'section list updated');
            
        } else
            return array('valid' => false, 'message' => 'make sure password exists and correct');
        
    }
    
    public function isExistsPassword()
    {
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();
        
        $status = $this->passwordSecurityService->getUserPasswordSecurity($userId);
        
        if ($status)
            return array('valid' => true, 'message' => 'exists password');
        else
            return array('valid' => false, 'message' => 'password not created');
        
    }
    
    public function isActivePassword()
    {
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();
        
        $status = $this->passwordSecurityService->isActivePasswordSecurity($userId);
        
        if (is_null($status))
        {
            return array('valid' => false,'exists'=>false, 'message' => 'password not created');
    
        }
        elseif ($status)
        {
            
            return array('valid' => true, 'message' => 'active password');
        }
        else
            
            return array('valid' => false, 'message' => 'password not active');
        
        
    }
    
    
    public function passwordSectionList()
    {
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();
    
        if (!isset($_POST['password'])) {
            return array('valid' => false, 'message' => 'password is require');
        }
    
        $status = $this->passwordSecurityService->checkUserPassword($userId, $_POST['password']);
    
        if ($status)
        {
            $sections = $this->passwordSecurityService->getSectionsList($userId);
            return array('valid' => true, 'message' => 'section list' , 'list'=>$sections);
        }
        else
        {
            return array('valid' => false, 'message' => 'make sure password exists and correct');
        }
    
    
    }
    
    public function isSectionSecure()
    {
    
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();
    
        if (!isset($_POST['password'])) {
            return array('valid' => false, 'message' => 'password is require');
        }
    
        if (!isset($_POST['section'])) {
            return array('valid' => false, 'message' => 'section is require');
        
        }
    
        $status = $this->passwordSecurityService->checkUserPassword($userId, $_POST['password']);
    
        if ($status)
        {
            $section_status = $this->passwordSecurityService->isSectionInList($userId ,$_POST['section']);
            return array('valid' => true, 'message' => 'section status' , 'status'=>$section_status);
        }
        else
        {
            return array('valid' => false, 'message' => 'make sure password exists and correct');
        }
    
    }
}
