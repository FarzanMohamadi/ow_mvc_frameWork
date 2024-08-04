<?php
$plugin = OW::getPluginManager()->getPlugin('birthdays');

$eventHandler = BIRTHDAYS_CLASS_EventHandler::getInstance();
$eventHandler->genericInit();

$eventHandler = BIRTHDAYS_MCLASS_EventHandler::getInstance();
$eventHandler->init();
