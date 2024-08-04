<?php
if (OW::getConfig()->configExists('frmmobileaccount', 'sign_up_page')) {
    OW::getConfig()->deleteConfig('frmmobileaccount', 'sign_up_page');
}