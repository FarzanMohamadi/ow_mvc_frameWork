<?php
class UtilStringTest extends FRMUnitTestUtilites
{
    private $truncateHtmlString;
    private $truncateString;
    private $TEST_USER_NAME = "user1";
    private $TEST_PASSWORD = '12345';
    private $TEST_EMAIL = "user1@gmail.com";
    private $userService;
    private $user;
    protected function setUp()
    {
        parent::setUp();
        $this->truncateHtmlString = "<p><b>This</b><a href=\"#\">is</a>test</p>";
        $this->truncateString = "This, is! test.";

        $this->userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser($this->TEST_USER_NAME, $this->TEST_EMAIL, $this->TEST_PASSWORD, "1987/3/21", "1", $accountType, 'c0de');
        $this->user =  $this->userService->findByUsername($this->TEST_USER_NAME);
    }

    public function testTruncateHtmlTest()
    {
        $decodedString = UTIL_String::truncate_html($this->truncateHtmlString, 5);
        self::assertEquals("<p><b>This</b><a href=\"#\">i</a></p>",$decodedString);
    }

    public function testTruncate()
    {
        $decodedString = UTIL_String::truncate($this->truncateString,2);
        self::assertEquals("This,",$decodedString);

        $decodedString = UTIL_String::truncate($this->truncateString,6);
        self::assertEquals("This,",$decodedString);

        $decodedString = UTIL_String::truncate($this->truncateString,7);
        self::assertEquals("This, is",$decodedString);

        $decodedString = UTIL_String::truncate($this->truncateString,10);
        self::assertEquals("This, is!",$decodedString);

        $decodedString = UTIL_String::truncate($this->truncateString,12);
        self::assertEquals("This, is! test",$decodedString);
    }

    public function testPrettify()
    {
        $decodedString = UTIL_String::prettify("#test");
        self::assertEquals(0,strpos($decodedString,"<a class=\"frmhashtag_tag english_tag\""));

        $decodedString = UTIL_String::prettify(":smile:");
        self::assertEquals(0,strpos($decodedString, "<img class=\"emj\" alt=\"😄\" src=\""));

        $decodedString = UTIL_String::prettify("@user1");
        self::assertEquals(0,strpos($decodedString, "<a class=\"frmmention_person\""));
    }

    public function tearDown()
    {
        FRMSecurityProvider::deleteUser($this->user->getUsername());
    }
}