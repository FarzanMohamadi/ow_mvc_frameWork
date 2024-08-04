<?php
interface IWebDriver
{
    function prepare();
    function url(string $url);
    function maximizeWindow();

    function byLinkText(string $linkText): IElement;
    function byCssSelector(string $cssSelector): IElement;
    function byXPath(string $xpath) : IElement;
    function byTag(string $tag) : IElement;
    function byClassName(string $className): IElement;
    function byId(string $id): IElement;
    function byName(string $name): IElement;

    function waitUntil($callback, $timeoutMillis = NULL);

    function close();


    function executeScript(string $script, array $arguments = []);
    function findElementsByCssSelector(string $cssSelector): array;

    function currentScreenshot();

    function deleteCookie(string $cookieName);
}
