<?php
class UtilHtmlTagTest extends FRMUnitTestUtilites
{
    private $decodeStringTags;
    private $decodeStringTagsAndJs;
    private $decodeStringJs;
    private $sanitizeString;
    private $autoLinkString;
    private $escapeJsString;
    protected function setUp()
    {
        parent::setUp();
        $this->decodeStringTags = "<a href=\"#\">Test</a>";
        $this->decodeStringTagsAndJs = "<a href=\"#\">Test</a><script>Test</script>";
        $this->decodeStringJs = "<script>Test</script>";
        $this->sanitizeString = "<a href='#'>test<b>test1<a href='#'>test2";
        $this->autoLinkString = "http://google.com";
        $this->escapeJsString = "<script>alert('Test')</script>";
    }

    public function testStripTags()
    {
        $decodedString1 = UTIL_HtmlTag::stripTags($this->decodeStringTags);
        self::assertEquals("Test", $decodedString1);
    }

    public function testStripTagsAndJs()
    {
        $decodedString2 = UTIL_HtmlTag::stripTagsAndJs($this->decodeStringTagsAndJs);
        self::assertEquals("Test", $decodedString2);
    }

    public function testStripJs()
    {
        $decodedString3 = UTIL_HtmlTag::stripJs($this->decodeStringJs);
        self::assertEquals("", $decodedString3);
    }

    public function testSanitize()
    {
        $decodedString4 = UTIL_HtmlTag::sanitize($this->sanitizeString);
        self::assertEquals("<a href=\"#\">test<b>test1<a href=\"#\">test2</a></b></a>", $decodedString4);
    }

    public function testAutoLink()
    {
        $decodedString5 = UTIL_HtmlTag::autoLink($this->autoLinkString);
        self::assertEquals("<a href=\"http://google.com\" class=\"ow_autolink\" target=\"_blank\" rel=\"nofollow\">http://google.com</a>", $decodedString5);
    }

    public function testEscapeJs()
    {
        $decodedString5 = UTIL_HtmlTag::escapeJs($this->escapeJsString);
        self::assertEquals("<script>alert(\'Test\')<\/script>", $decodedString5);
    }

    public function tearDown()
    {

    }
}