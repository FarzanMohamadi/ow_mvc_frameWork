<?php
interface IElement
{
    function click();

    function displayed();

    function clear();

    function value(string $value);

    function attribute(string $attributeName): string;

    function byName(string $string): IElement;

    function submit();
}