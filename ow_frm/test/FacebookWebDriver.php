<?php
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;

require_once "IWebDriver.php";
require_once "IElement.php";

class FacebookWebDriver implements IWebDriver
{
    /**
     * @var RemoteWebDriver
     */
    public $webDriver;

    static private $W = 1000;
    static private $H = 1400;

    public function __construct(string $browserName)
    {
        $capabilities = array(WebDriverCapabilityType::BROWSER_NAME => $browserName);
        // selenium-server-standalone-#.jar (version 2.x or 3.x)
        $host = 'http://localhost:4444/wd/hub';
        // selenium-server-standalone-#.jar (version 4.x)
//        $host = 'http://localhost:4444';

        if($browserName=='chrome')
        {
            $options = new \Facebook\WebDriver\Chrome\ChromeOptions();
            $options->addArguments(array(
                '--no-sandbox',
                '--headless',
                '--disable-gpu',
                '--disable-dev-shm-usage'
            ));

            $capabilities = \Facebook\WebDriver\Remote\DesiredCapabilities::chrome();
            $capabilities->setPlatform("Linux");
            $capabilities->setCapability('acceptSslCerts', false);
            $capabilities->setCapability(\Facebook\WebDriver\Chrome\ChromeOptions::CAPABILITY, $options);

            $capabilities = [
                'goog:chromeOptions' => [
                    'w3c' => false,
                    'args' => [
                        '--no-sandbox',
                        '--headless',
                        '--disable-dev-shm-usage',
                        '--window-size=1600,1200'
                    ],
                ],
                'timeouts' => [
                    'pageLoad' => 980000,
                ]
            ];


            $host = 'http://localhost:4444';
        }
        elseif($browserName=='firefox')
        {
            $capabilities = \Facebook\WebDriver\Remote\DesiredCapabilities::firefox();
            $capabilities->setPlatform("Linux");
            $capabilities->setCapability('acceptSslCerts', false);
            $capabilities->setCapability("pageLoadStrategy", "eager"); #  eager
            $capabilities->setCapability('moz:firefoxOptions', ['args' =>
                [
                    '--no-sandbox',
                    '--headless',
                    '--disable-gpu',
                    '--window-size='.self::$W.','.self::$H,
//                '--disable-dev-shm-usage'
                ]]);

            $host = 'http://localhost:4444';
        }

        $this->webDriver = RemoteWebDriver::create($host, $capabilities, 25000, 35000);
    }

    function url(string $url)
    {
        $this->webDriver->get($url);
    }

    public function prepare()
    {
    }

    function close()
    {
        $this->webDriver->close();
        $this->webDriver->quit();
    }

    function maximizeWindow()
    {
//        $this->webDriver->manage()->window()->maximize();
        $this->webDriver->manage()->window()->setSize(new WebDriverDimension(self::$W, self::$H));
    }

    private function findElement(WebDriverBy $search): FacebookElement {
        return new FacebookElement($this->webDriver->findElement($search));
    }

    function byXPath(string $xpath): IElement
    {
        return $this->findElement(WebDriverBy::xpath($xpath));
    }

    function byLinkText(string $linkText): IElement
    {
        return $this->findElement(WebDriverBy::linkText($linkText));
    }

    function byCssSelector(string $cssSelector): IElement
    {
        return $this->findElement(WebDriverBy::cssSelector($cssSelector));
    }

    function byTag(string $tag): IElement
    {
        return $this->findElement(WebDriverBy::tagName($tag));
    }

    function byClassName(string $className): IElement
    {
        return $this->findElement(WebDriverBy::className($className));
    }

    function byId(string $id): IElement
    {
        return $this->findElement(WebDriverBy::id($id));
    }

    function byName(string $name): IElement
    {
        return $this->findElement(WebDriverBy::name($name));
    }

    /**
     * @param $callback
     * @param null $timeoutMillis
     * @return mixed
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    function waitUntil($callback, $timeoutMillis = NULL)
    {
        return $this->webDriver->wait(intdiv($timeoutMillis, 1000))
            ->until($callback);
    }

    function executeScript(string $script, array $arguments = [])
    {
        $this->webDriver->executeScript($script, $arguments);
    }

    function findElementsByCssSelector(string $cssSelector): array
    {
        $elements = $this->webDriver->findElements(WebDriverBy::cssSelector($cssSelector));
        return array_map(function($x) { return new FacebookElement($x); }, $elements);
    }

    function currentScreenshot()
    {
        return $this->webDriver->takeScreenshot();
    }

    function deleteCookie(string $cookieName)
    {
        $this->webDriver->manage()->deleteCookieNamed($cookieName);
    }

    function getCookie(string $cookieName)
    {
        return $this->webDriver->manage()->getCookieNamed($cookieName);
    }

    function getAttributeById($id,$attributeName)
    {
        return $this->webDriver->findElement(WebDriverBy::id($id))->getAttribute($attributeName);
    }

    function getAttributeByName($name,$attributeName)
    {
        return $this->webDriver->findElement(WebDriverBy::name($name))->getAttribute($attributeName);
    }

    function getAttributeByXPath($xpath,$attributeName)
    {
        return $this->webDriver->findElement(WebDriverBy::xpath($xpath))->getAttribute($attributeName);
    }

    public function keys($key)
    {
        $this->webDriver->getKeyboard()->sendKeys($key);
    }

}

class FacebookElement implements IElement {
    /**
     * @var RemoteWebElement
     */
    private $element;

    /**
     * @param $element RemoteWebElement
     */
    public function __construct($element)
    {
        $this->element = $element;
    }

    function click()
    {
        $this->element->click();
    }

    function displayed()
    {
        return $this->element->isDisplayed();
    }

    function clear()
    {
        $this->element->clear();
    }

    function value(string $value)
    {
        $this->element->sendKeys($value);
    }

    function attribute(string $attributeName): string
    {
        return $this->element->getAttribute($attributeName);
    }

    private function findElement(WebDriverBy $search): FacebookElement {
        return new FacebookElement($this->element->findElement($search));
    }

    function byName(string $name): IElement
    {
        return $this->findElement(WebDriverBy::name($name));
    }

    function byClassName(string $className): IElement
    {
        return $this->findElement(WebDriverBy::className($className));
    }

    function byXPath(string $xPath): IElement
    {
        return $this->findElement(WebDriverBy::xpath($xPath));
    }

    function submit()
    {
        $this->element->submit();
    }

}
