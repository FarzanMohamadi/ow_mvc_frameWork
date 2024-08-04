<?php
if (!OW::getConfig()->configExists('frmadvancesearch', 'show_entity_author')){
    OW::getConfig()->saveConfig('frmadvancesearch', 'show_entity_author', true);
}
OW::getConfig()->saveConfig('frmadvancesearch', 'show_search_to_guest', false);