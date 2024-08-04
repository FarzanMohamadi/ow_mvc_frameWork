<?php
if (!OW::getConfig()->configExists('frmmobileaccount', 'username_prefix')){
    OW::getConfig()->saveConfig('frmmobileaccount', 'username_prefix', 'shub_user_');
}

if (!OW::getConfig()->configExists('frmmobileaccount', 'email_postfix')){
    OW::getConfig()->saveConfig('frmmobileaccount', 'email_postfix', '@shub.frmcenter.ir');
}
