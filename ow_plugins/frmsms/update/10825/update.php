<?php
if (!OW::getConfig()->configExists('frmsms', 'token_resend_interval')){
    OW::getConfig()->saveConfig('frmsms', 'token_resend_interval', 1);
}

if (!OW::getConfig()->configExists('frmsms', 'max_token_request')){
    OW::getConfig()->saveConfig('frmsms', 'max_token_request', 10);
}