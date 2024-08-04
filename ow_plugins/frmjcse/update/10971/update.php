<?php
/**
 * Created by PhpStorm.
 * User: Ali Khatami
 * Date: 11/26/2018
 * Time: 3:54 PM
 */


$path = OW::getPluginManager()->getPlugin('frmjcse')->getRootDir() . 'langs.zip';
Updater::getLanguageService()->importPrefixFromZip($path, 'frmjcse');
