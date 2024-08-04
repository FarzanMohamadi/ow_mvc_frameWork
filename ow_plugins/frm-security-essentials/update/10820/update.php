<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

try {
    Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'frmsecurityessentials');
}catch(Exception $e){

}