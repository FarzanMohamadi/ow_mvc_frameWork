<?php
class frmAllUsersPasswordInvalidTest extends FRMTestUtilites
{
    private $TEST_USERNAME = 'adminForLoginTest';
    private $TEST_EMAIL = 'admin@gmail.com';
    private $TEST_CORRECT_PASSWORD = 'asdf@1111';
    private $user;

    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('frmpasswordchangeinterval'));
        ensure_session_active();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USERNAME,$this->TEST_EMAIL,$this->TEST_CORRECT_PASSWORD,"1987/3/21","1",$accountType,'c0de');
        $this->user = BOL_UserService::getInstance()->findByUsername($this->TEST_USERNAME);
    }

    public function testAllUsersPasswordInvalid()
    {
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $this->url(OW_URL_HOME . "dashboard");

        $sessionId = $this->webDriver->getCookie(OW_Session::getInstance()->getName())['value'];
        $sessionId = str_replace('%2C', ',', $sessionId); // fixes  '/^[-,a-zA-Z0-9]{1,128}$/'
        $this->sign_in($this->user->getUsername(),$this->TEST_CORRECT_PASSWORD,true,true,$sessionId);

        srand(time());
        $number = rand();
        try{
            //$this->waitUntilElementLoaded('byClassName','ow_message_node');
            $service = FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance();
            $service->setAllUsersPasswordInvalid(false);
            try{
                $backupTable = OW::getDbo()->queryForRow('show tables like :tableName', array('tableName' => OW_DB_PREFIX.'frmpasswordchangeinterval_password_validation'));
                if (!empty($backupTable)) {
                    $queryForGetData = "select token from `".OW_DB_PREFIX."frmpasswordchangeinterval_password_validation` where userid ='".$this->user->getId()."'";
                    $data = OW::getDbo()->queryForRow($queryForGetData);
                    if($data)
                    {
                        $url =  "frmpasswordchangeinterval/checkvalidatepassword/".$data['token'];
                        sleep(5);
                        $this->webDriver->executeScript('window.location.replace('.'"'.$url.'"'.')',array());
                    }
                    $this->waitUntilElementLoaded('byName','password');
                    $this->byName('password')->value('selenium test'.$number);
                    $this->byName('repeatPassword')->value('selenium test'.$number);
                    $this->byName('submit')->submit();
                    self::assertTrue(true);
                }
                else
                {
                    self::assertTrue(false);
                }
            }catch (Exception $ex){
                $this->handleException($ex,'',true,false);
            }

            self::assertTrue(true);

        }catch (Exception $ex){
            $this->handleException($ex,'',true,false);
        }
    }

    public function deleteUserFromPassChangedTable()
    {
        try{
            $backupTable = OW::getDbo()->queryForRow('show tables like :tableName', array('tableName' => OW_DB_PREFIX.'frmpasswordchangeinterval_password_validation'));
            if (!empty($backupTable)) {
                $queryForGetData = "delete  from `".OW_DB_PREFIX."frmpasswordchangeinterval_password_validation` where userid ='".$this->user->getId()."'";
                $data = OW::getDbo()->queryForRow($queryForGetData);
            }
        }catch (Exception $ex) {
            return null;
        }
    }
    public function tearDown()
    {
        if($this->isSkipped)
            return;

        //delete users
        FRMSecurityProvider::deleteUser($this->user->getUsername());
        parent::tearDown();
    }
}