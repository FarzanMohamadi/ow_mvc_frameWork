<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 7/13/2017
 * Time: 12:33 PM
 */
class removeExpiredLoginCookiesTest extends FRMUnitTestUtilites
{
    private static $USER_NAME_PREFIX = 'user_cookie_';
    private static $PASSWORD = 'password123';

    /**
     * @var BOL_LoginCookieDao
     */
    private $loginCookieDao;
    /**
     * @var BOL_User
     */
    private $user1;
    /**
     * @var BOL_User
     */
    private $user2;
    /**
     * @var BOL_User
     */
    private $user3;

    protected function setUp()
    {
        parent::setUp();
        $this->loginCookieDao = BOL_LoginCookieDao::getInstance();

        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;

        $userName = uniqid(self::$USER_NAME_PREFIX);
        FRMSecurityProvider::createUser($userName, $userName.'@gmail.com', self::$PASSWORD, "1987/3/21", "1", $accountType);
        $this->user1 = BOL_UserService::getInstance()->findByUsername($userName);
        $userName = uniqid(self::$USER_NAME_PREFIX);
        FRMSecurityProvider::createUser($userName, $userName.'@gmail.com', self::$PASSWORD, "1987/3/21", "1", $accountType);
        $this->user2 = BOL_UserService::getInstance()->findByUsername($userName);
        $userName = uniqid(self::$USER_NAME_PREFIX);
        FRMSecurityProvider::createUser($userName, $userName.'@gmail.com', self::$PASSWORD, "1987/3/21", "1", $accountType);
        $this->user3 = BOL_UserService::getInstance()->findByUsername($userName);
    }

    public function test()
    {
        $t = (int)(time()-60);
        $cookieStringUser1 = BOL_UserService::getInstance()->saveLoginCookie($this->user1->id,$t);
        $cookie = $this->loginCookieDao->findByCookie($cookieStringUser1->cookie);
        //user1 login cookie must exists
        self::assertTrue(isset($cookie));

        $t = (int)(time()+3600);
        $cookieStringUser2 = BOL_UserService::getInstance()->saveLoginCookie($this->user2->id,$t);
        $cookie = $this->loginCookieDao->findByCookie($cookieStringUser2->cookie);
        //user2 login cookie must exists
        self::assertTrue(isset($cookie));

        $t = (int)(time()-1);
        $cookieStringUser3 = BOL_UserService::getInstance()->saveLoginCookie($this->user3->id,$t);
        $cookie = $this->loginCookieDao->findByCookie($cookieStringUser3->cookie);
        //user3 login cookie must exists
        self::assertTrue(isset($cookie));

        BOL_UserService::getInstance()->removeExpiredLoginCookies();

        $cookie = $this->loginCookieDao->findByCookie($cookieStringUser1->cookie);
//        user1 login cookie should not exists because it is expired
        self::assertFalse(isset($cookie));

        $cookie = $this->loginCookieDao->findByCookie($cookieStringUser2->cookie);
//        user2 login cookie must exists because it is not expired
        self::assertTrue(isset($cookie));

        $cookie = $this->loginCookieDao->findByCookie($cookieStringUser3->cookie);
//        user3 login cookie should not exists because it is expired
        self::assertFalse(isset($cookie));
    }

    protected function tearDown()
    {
        parent::tearDown();
        FRMSecurityProvider::deleteUser($this->user1->getUsername());
        FRMSecurityProvider::deleteUser($this->user2->getUsername());
        FRMSecurityProvider::deleteUser($this->user3->getUsername());
    }

}