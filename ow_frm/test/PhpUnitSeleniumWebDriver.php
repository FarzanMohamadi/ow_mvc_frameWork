<?php
class PhpUnitSeleniumWebDriver implements IWebDriver
{
    /**
     * @var PHPUnit_Extensions_Selenium2TestCase
     */
    private $webDriver;
    /**
     * @var PHPUnit_Extensions_Selenium2TestCase_Session
     */
    private $session;

    public function __construct(string $browserName)
    {
        $this->webDriver->setBrowser($browserName);
        if ($browserName === "chrome") {
            $this->webDriver->setDesiredCapabilities([
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
            ]);
        }
        if (defined('OW_URL_HOME')) {
            $this->webDriver->setBrowserUrl(OW_URL_HOME);
        }
    }

    public function prepare()
    {
        $this->session = $this->webDriver->prepareSession();
    }

    function url(string $url)
    {
        $this->webDriver->url($url);
    }

    function maximizeWindow()
    {
        $this->session->currentWindow()->maximize();
    }

    private function findElement($method, $value): PhpUnitSeleniumElement {
        return new PhpUnitSeleniumElement($this->webDriver->$method($value));
    }

    public function byXPath(string $xpath): IElement
    {
        return $this->findElement("byXPath", $xpath);
    }

    function byLinkText(string $linkText): IElement
    {
        return $this->findElement("byLinkText", $linkText);
    }

    function byCssSelector(string $cssSelector): IElement
    {
        return $this->findElement("byCssSelector", $cssSelector);
    }

    function byTag(string $tag): IElement
    {
        return $this->findElement("byTag", $tag);
    }

    function byClassName(string $className): IElement
    {
        return $this->findElement("byClassName", $className);
    }

    function byId(string $id): IElement
    {
        return $this->findElement("byId", $id);
    }

    function byName(string $name): IElement
    {
        return $this->findElement("byName", $name);
    }

    function close()
    {
    }

    function waitUntil($callback, $timeoutMillis = NULL)
    {
        return $this->webDriver->waitUntil($callback, $timeoutMillis);
    }

    function executeScript(string $script, array $arguments = [])
    {
        $this->webDriver->execute(array(
            'script' => $script,
            'args' => $arguments,
        ));
    }

    function findElementsByCssSelector(string $cssSelector): array
    {
        $elements = $this->webDriver->elements($this->webDriver->using('css selector')->value($cssSelector));
        return array_map(function($x) { return new PhpUnitSeleniumElement($x); }, $elements);
    }

    function currentScreenshot()
    {
        return $this->webDriver->currentScreenshot();
    }

    function deleteCookie(string $cookieName)
    {
        $this->session->cookie()->remove($cookieName);
    }
}

class PhpUnitSeleniumElement implements IElement {
    /**
     * @var PHPUnit_Extensions_Selenium2TestCase_Element
     */
    private $element;

    /**
     * @param $element PHPUnit_Extensions_Selenium2TestCase_Element
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
        return $this->element->displayed();
    }

    function clear()
    {
        $this->element->clear();
    }

    function value(string $value)
    {
        $this->element->value($value);
    }

    function attribute(string $attributeName): string
    {
        $this->element->attribute($attributeName);
    }

    function byName(string $name): IElement
    {
        $this->element->byName($name);
    }

    function submit()
    {
        $this->element->submit();
    }
}
