<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
try {
    OW::getConfig()->saveConfig('frmmobilesupport', 'custom_download_link_code', '<a class="app_download_link android" href="/mobile-app/latest/native" target="_blank"></a>');
}catch (Exception $ex){}