<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 2/25/18
 * Time: 2:50 PM
 */

$eventHandler = new FRMQUESTIONS_MCLASS_EventHandler();
$eventHandler->init();

$urlMapping = new FRMQUESTIONS_CLASS_UrlMapping();
$urlMapping->mobileInit();