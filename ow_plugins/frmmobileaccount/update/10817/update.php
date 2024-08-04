<?php
if (!OW::getConfig()->configExists('frmmobileaccount', 'sign_up_page')) {
    OW::getConfig()->saveConfig('frmmobileaccount', 'sign_up_page', true);
}