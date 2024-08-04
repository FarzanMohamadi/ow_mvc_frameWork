<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
try {
    if (!OW::getConfig()->configExists('frmmobilesupport', 'web_config')) {
        OW::getConfig()->saveConfig('frmmobilesupport', 'web_config', '');
    }
    if (!OW::getConfig()->configExists('frmmobilesupport', 'web_key')) {
        OW::getConfig()->saveConfig('frmmobilesupport', 'web_key', '');
    }
}catch (Exception $ex){}